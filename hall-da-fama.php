<?php 

/**
 * Plugin Name:       Hall da Fama
 * Plugin URI:        https://https://github.com/rodrigowolfgang47
 * Description:       Esse plugin consome apis do google sheets.
 * Version:           1.0.0 
 * Author:            Rodrigo Costa
 * Author URI:        https://https://github.com/rodrigowolfgang47
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hall-da-fama
 */

defined('ABSPATH') or die;

add_action( 'admin_menu', 'techiepress_add_menu_page' );

function techiepress_add_menu_page(){
    add_menu_page( 
        'Hall-da-fama-api',
        'Hall-da-fama-api',
        'manage_options',
        'hall-da-fama',
        'run_all_fuction',
        'dashicons-rest-api',
        16
    );
}

function run_all_fuction(){

    
    if(false === get_option( 'hall_da_fama_pluggin_version' ) and false === get_option( 'icones_hall_da_fama' ) ){
        
        $google_sheet_data = get_goooglesheet_data();
        create_database_table();
        create_database_table_icon();
        create_database_table_position();
        add_new_stundents($google_sheet_data);
        add_all_icon_in_db_hall_da_fama($google_sheet_data);
        add_all_positions();
        return;
    }

    update_varification();

    return create_html_tables();

}

function update_varification(){
    global $wpdb;

    $ultima_atualizacao = $google_sheet[0]['Horas'];
    date_default_timezone_set('America/Sao_Paulo');
    $hora_atual = date('H');

    // $google_sheet_data = get_goooglesheet_data();
    
    if($hora_atual >= 4 and $hora_atual < 5){
        $google_sheet_data = get_goooglesheet_data();

        add_new_stundents($google_sheet_data);
        update_db($google_sheet_data);
        update_all_icon($google_sheet_data);
        delite_non_stundents();
        add_new_position();           
        delite_non_stundents_position();
        update_positions();
    }
}

function get_goooglesheet_data(){

    // Consulta uma planilha do google sheets
    $result = false;
    $query = 'Comunidade Cisco';
	$key = 'AIzaSyCtTDb_35n3CwNeWtJSX5ofnN4b71APLQI';
	$sheet = "1sTI0dvExeyX3tehB7k1pybg0XLlXfihnZexQ0rlxSlA";
    $url = "https://sheets.googleapis.com/v4/spreadsheets/{$sheet}/values/{$query}?key={$key}";
    
    $args = array(
        'headers' => array(
            'Content-Type' => 'application/json',
        ),
        'body'    => array(),
    );

    $connection = wp_remote_get($url, $args);
    $response_code = wp_remote_retrieve_response_code( $connection );
    
    if ( ! is_wp_error( $connection ) ) {
        $body =  wp_remote_retrieve_body(( $connection ), true );
        $body = json_decode($body);
    }

    if (401 === $response_code ){
        return "Acesso desconhecido";
    }

    if (200 !== $response_code ){
        return "Erro na api";
    }

    if (200 === $response_code ){
        $body = format_values_api($body);
        return $body;
    }

}

function format_values_api($body_json){

    $all_students = $body_json-> values;

    $number_of_students = count($all_students);

    $title = $body_json->values[0];

    $itens_Count = count($title);

    $all_students_dictionary = [];

    for($j = 1; $j < $number_of_students; $j++){

        $students_dictionary = [];

        $student = $all_students[$j];

        for($i = 0; $i <  $itens_Count; $i++){

            $students_dictionary[$title[$i]] = $student[$i];
        }

        array_push($all_students_dictionary, $students_dictionary);

    }
    return $all_students_dictionary;
}

function create_database_table(){
    
    global $hall_da_fama_pluggin_version;

    $hall_da_fama_pluggin_version = "1.0";
    
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'hall_da_fama_pluggin_version';

    $charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		nome text NOT NULL,
		img text,
		email text NOT NULL,
		pontos float NOT NULL,
		linkedin text NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
    
    add_option( "hall_da_fama_pluggin_version", $hall_da_fama_pluggin_version );
}

function create_database_table_position(){
    
    global $positions;

    $table_references_name = 'hall_da_fama_pluggin_version';

    $positions = "1.0";
    
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'positions';

    $charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        position int NOT NULL,
        aluno text NOT NULL
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
    
    add_option( "positions", $positions );
}


function create_database_table_icon(){
    
    global $icones_hall_da_fama;

    $icones_hall_da_fama = "1.0";
    
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'icones_hall_da_fama';

    $charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE $table_name (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		email text,
        ccna text,
		ccnp text,
		ccnp_enarsi text,
		ccnp_encor text,
		ipv6 text,
		mpls text,
		sd_wan text,
		troubleshooting text,
		bgp text,
		data_center text,
		marco_zero text,
		multicast text,
		qos text,
		ospf text,
		eigrp text,
		wifi text,
		is_is text,
		service_provider text,
		cloud_aws text,
        cloud_marco_zero text,
        cloud_virtualizacao text,
        aws_zero text,
        aws_associate text,
        ciber_marco_zero text,
        nse1 text,
        nse2 text,
        nse3 text,
        nse4 text,
        nse7 text,
        mk_marco_zero text,
        mk_mtcna text,
        mk_bgp text,
        mk_ipv6 text,
        mtcre text,
		PRIMARY KEY  (id)
	) $charset_collate;";


	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
    
    add_option( "icones_hall_da_fama", $icones_hall_da_fama );
}


function is_email_in_db($query_search){

    // Voc?? pode consultar os campos das tavelas aqui
    // Campos recomendados s??o e-mail ou nome

    global $wpdb;
            
    $table_name = $wpdb->prefix . 'hall_da_fama_pluggin_version';
    
    $query = "SELECT email FROM $table_name WHERE email = '$query_search' ";

    $result = $wpdb->get_results($query);

    if(count($result) > 0){
        return true;
    }

    return false;

}

function is_email_in_db_position($query_search, $table_name){

    // Voc?? pode consultar os campos das tavelas aqui
    // Campos recomendados s??o e-mail ou nome

    global $wpdb;
            
    $table = $wpdb->prefix . "$table_name";
    
    $query = "SELECT aluno FROM $table WHERE aluno = '$query_search' ";

    $result = $wpdb->get_results($query);

    if(count($result) > 0){
        return true;
    }else{
        return false;
    }

}

function add_new_stundents($get_goooglesheet){

    $google_sheet_data = $get_goooglesheet;
    
    $sheet_rage = count($google_sheet_data);
    
    for ($i = 0; $i < $sheet_rage; $i++){
        
        $current_student = $google_sheet_data[$i];
        
        $current_email = $google_sheet_data[$i]['Email'];
        
        $is_student_in_db = is_email_in_db($current_email);
        
        if(false === $is_student_in_db){
            add_value_in_db_hall_da_fama($current_student);
        }      
    }
    
    return;

}

function add_new_position(){

    global $wpdb;

    $table_name_position = 'positions';

    $table_name = $wpdb->prefix . 'hall_da_fama_pluggin_version';

    $query = "SELECT email FROM $table_name";

    $results = $wpdb->get_results($query);
    
    $table_name = $wpdb->prefix . 'positions';



    foreach($results as $k => $v){

        $is_email = is_email_in_db_position($v->email, $table_name_position);
        

        if(false == $is_email){

            $query = "SELECT * FROM $table_name";

            $total_query = "SELECT COUNT(1) FROM (${query}) AS combined_table";
        
            $total = $wpdb->get_var( $total_query );
        
            $rankig = $total + 1;

            add_value_in_db_positions($v->email, $rankig);
        }
    }
}
    
function add_value_in_db_hall_da_fama($current_student){
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'hall_da_fama_pluggin_version';

    if($current_student["Linkedin"] != ""){
        $linkedin = $current_student["Linkedin"];
    }else{
        $linkedin = "#";
    }

    
    $status =  $wpdb->insert(
        $table_name,
        array( 
            'time' => current_time( 'mysql' ), 
            'nome' =>  $current_student["Nome Completo"], 
            'img' =>  $current_student["imagem"], 
            'email' => $current_student["Email"], 
            'pontos' => $current_student["Pontos"], 
            'linkedin' => $linkedin, 
            ) 
        );
        
}

function add_value_in_db_positions($current_student, $position){
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'positions';

    $status =  $wpdb->insert(
        $table_name,
        array(
            'position' => $position,
            'aluno' => $current_student
            ) 
    );
}

function add_all_positions(){

    global $wpdb;

    $table_name_hall = $wpdb->prefix . 'hall_da_fama_pluggin_version';

    $query = "SELECT * FROM $table_name_hall ORDER BY pontos DESC";

    $results = $wpdb->get_results($query);
    
    $table_name = $wpdb->prefix . 'positions';

    $i = 1;

    foreach($results as $result){

        $status =  $wpdb->insert(
            $table_name,
            array( 
                'position' => $i,
                'aluno' => $result->email
                )
        );

        $i++;
    }

}



function update_db($google_sheet){
    
    global $wpdb;

    $google_sheet_data = $google_sheet;

    $table_name = $wpdb->prefix . 'hall_da_fama_pluggin_version';

    foreach($google_sheet_data as $data){

        $email = $data['Email'];
        $current_points = $data['Pontos'];
        $current_name = $data['Nome Completo'];
        $current_img = $data['imagem'];

        if($data["Linkedin"] != ""){
            $current_linkedin = $data["Linkedin"];
        }else{
            $current_linkedin = "#";
        }
        
        $results = $wpdb->get_row("SELECT * FROM $table_name WHERE email = '$email' ");



        if ($results->pontos != $current_points){

            $status = $wpdb->update($table_name, 
            array(
                'pontos' => $current_points,
                'nome'-> $current_name, 
                'img'-> $current_img, 
                'linkedin'-> $current_linkedin

            ), array('id' => $results->id) );
        }

    }
     
}

function create_html_tables(){

    $term = $_GET['search'];

    $table_title = "<table class='main_table'><tr class='table-title' ><td class='ranking-title'>Ranking</td><td class='nome-title'>Nome</td><td class='score-title'>Score</td><td>Conquistas</td><td class='linkedin-title'>Linkedin</td></tr>";

    global $wpdb;                

    $table_name = $wpdb->prefix . 'hall_da_fama_pluggin_version';  
    $table_name_icon = $wpdb->prefix . 'icones_hall_da_fama';
                    
    $table_info = itens_per_page($table_name);

    $all_tables_data;

    $positions = $wpdb->prefix . 'positions';

    if(isset($term)){
        $all_tables_data = do_a_search($term, $table_name);

    }else{
        foreach($table_info as $info){
            
            $all_icons = "";

            $result = $wpdb->get_row("SELECT * FROM $table_name_icon WHERE email = '$info->email' ");

            $result_position = $wpdb->get_row("SELECT position FROM $positions WHERE aluno = '$info->email'");

            $index = 0;

            foreach($result as $k => $v){
                if($index >= 2){
                    $arr_alt = explode("/", $v);
                    $alt = strtolower(str_replace(".png", "", $arr_alt[7]));
                    $all_icons .= "<img src='$v' alt='$alt' title='$alt' style='max-width: 24px;'>";
                }    
                $index++;
            }

            if($info->img){
                $all_tables_data .= "<tr><td class='classfication'>$result_position->position ??</td><td class='nome'><img src='$info->img' alt='foto' style='max-width: 45px; border-radius: 100px;'><h4 class='nome-text' >$info->nome</h4></td><td class='pontos'>$info->pontos</td><td class= 'icones-conquistas'><div class='icones-container'>$all_icons</div></td><td class='linkedin-h'><a href='$info->linkedin' target='_blank'><img src='https://hackone.com.br/wp-content/uploads/2022/10/linkedin.png' style='max-width: 30px; border: none;'></a></td></tr>";
            }else{
                $all_tables_data .= "<tr><td class='classfication'>$result_position->position ??</td><td class='nome'><img src='https://hackone.com.br/wp-content/uploads/2022/10/blank-profile-picture-gb359e0966_640-1.png' alt='foto' style='max-width: 45px; border-radius: 100px;'><h4>$info->nome</h4></td><td class='pontos'>$info->pontos</td><td class= 'icones-conquistas'><div class='icones-container'>$all_icons</div></td><td class='linkedin-h'><a href='$info->linkedin' target='_blank'><img src='https://hackone.com.br/wp-content/uploads/2022/10/linkedin.png' style='max-width: 30px; border: none;'></a></td></tr>";
            }


        }
    }


    $complete_table = "$table_title  $all_tables_data </table>";

    return $complete_table;
}

function create_html_tables_mobile(){

    $term = $_GET['search'];

    global $wpdb;
    
    $table_title = "<div class='content-mb'><div class='title'><h3 class='ranking'>Ranking</h3><h3 class='nome'>Nome</h3></div>";

    $table_name = $wpdb->prefix . 'hall_da_fama_pluggin_version';  
    $table_name_icon = $wpdb->prefix . 'icones_hall_da_fama';
                    
    $table_info = itens_per_page($table_name);

    $all_tables_data;

    $positions = $wpdb->prefix . 'positions';

    if(isset($term)){
        $all_tables_data = do_a_search_mobile($term, $table_name);

    }else{
        foreach($table_info as $info){
            
            $all_icons = "";

            $result = $wpdb->get_row("SELECT * FROM $table_name_icon WHERE email = '$info->email' ");

            $result_position = $wpdb->get_row("SELECT position FROM $positions WHERE aluno = '$info->email'");

            $index = 0;

            foreach($result as $k => $v){
                if($index >= 2){
                    $arr_alt = explode("/", $v);
                    $alt = strtolower(str_replace(".png", "", $arr_alt[7]));
                    $all_icons .= "<img src='$v' alt='$alt' title='$alt' style='max-width: 24px;'>";
                }    
                $index++;
            }

            if($info->img){
                $all_tables_data .= "
                    <div class='students'>
                        <div class='estudantes'>
                            <h3 class='ranking'>$result_position->position ??</h3>
                            <section class='nome'>
                                <img src='$info->img'
                                    alt='Foto'>
                                <h3 class='nome-text' >$info->nome</h3>
                            </section>
                        </div>
                        <div class='score-conquistas'>
                            <div class='title-other'>
                                <div>
                                    Score
                                </div>
                                <div>
                                    Linkedin
                                </div>
                                <div class='conquistas'>
                                    Conquistas
                                </div>
                            </div>
                            <div class='conquitas-score'>
                                <div class='score'>$info->pontos</div>
                                <div class='linkedin'>
                                    <a href='$info->linkedin' target='_blank'><img src='https://hackone.com.br/wp-content/uploads/2022/10/linkedin.png' style='max-width: 30px; border: none;'></a>
                                </div>
                                <div class='conquistas-itens scroll'>
                                    $all_icons
                                </div>
                            </div>
                        </div>
                    </div>
                ";
                
            }else{
                $all_tables_data .= 
                "
                <div class='students'>
                    <div class='estudantes'>
                        <h3 class='ranking'>$result_position->position ??</h3>
                        <section class='nome'>
                            <img src='https://hackone.com.br/wp-content/uploads/2022/10/blank-profile-picture-gb359e0966_640-1.png'
                                alt='Foto'>
                            <h3 class='nome-text' >$info->nome</h3>
                        </section>
                    </div>
                    <div class='score-conquistas'>
                        <div class='title-other'>
                            <div>
                                Score
                            </div>
                            <div>
                                Linkedin
                            </div>
                            <div class='conquistas'>
                                Conquistas
                            </div>
                        </div>
                        <div class='conquitas-score'>
                            <div class='score'>$info->pontos</div>
                            <div class='linkedin'>
                                <a href='$info->linkedin' target='_blank'><img src='https://hackone.com.br/wp-content/uploads/2022/10/linkedin.png' style='max-width: 30px; border: none;'></a>
                            </div>
                            <div class='conquistas-itens scroll'>
                                $all_icons
                            </div>
                        </div>
                    </div>
                </div>
                ";
            }


        }
    }


    $complete_table = "$table_title $all_tables_data </div>";

    return $complete_table;
}


function verify_non_stundents(){

    $google_sheet_data = get_goooglesheet_data();

    $sheet_rage = count($google_sheet_data);

    $email_in_sheet = array();

    $not_active = array();

    for ($i = 0; $i < $sheet_rage; $i++){
        
        $email = $google_sheet_data[$i]['Email'];
        array_push($email_in_sheet, $email);
    }
    
    global $wpdb;
            
    $table_name = $wpdb->prefix . 'hall_da_fama_pluggin_version';
    
    $query = "SELECT email FROM $table_name";

    $results = $wpdb->get_results($query);


    foreach($results as $result){
        $is_stundent_active = in_array($result->email, $email_in_sheet);

        if(! $is_stundent_active ){
            array_push($not_active, $result->email);
        }
    }

    return $not_active;
    
}

function delite_non_stundents(){

    $non_students_list = verify_non_stundents();

    if(count($non_students_list) > 0){

        global $wpdb;
            
        $table_name = $wpdb->prefix . 'hall_da_fama_pluggin_version';
    
        foreach($non_students_list as $non_student){
    
            $results = $wpdb->get_row("SELECT * FROM $table_name WHERE email = '$non_student' ");
    
            $deleted =  $wpdb->delete($table_name, array( 'id' => $results->id ));  
    
        }
    }

    return;
    
}

function delite_non_stundents_position(){

    global $wpdb;

    $table_name = $wpdb->prefix . 'positions';

    $query = "SELECT aluno FROM $table_name";

    $results = $wpdb->get_results($query);

    foreach($results as $k){

        $is_email = is_email_in_db($k->aluno);


        if(false == $is_email){

            $results = $wpdb->get_row("SELECT * FROM $table_name WHERE aluno = '$k->aluno' ");
    
            $deleted =  $wpdb->delete($table_name, array( 'id' => $results->id )); 
        }
    }

    return;
    
}

function search_bar(){
    $search_form = "
    <form class = 'form-bar'  method='get' action='' style='
    display: flex;
    justify-content: center;
    align-items: flex-end;
    justify-content: end;
    margin: 20px 0px;
    gap: 1rem;'>
    <input type='text' name='search' class='campo-de-pesquisa'  >
    <input class='pesquisa' type='submit' value='Pesquisar'>
    </form>
    <li class = 'form-error' style ='display:none;'>O campo precisa conter algum valor</li>
    ";

    $term = $_GET['search'];

    if(isset($_GET['search'])){
        $search_form = "
        <form class = 'form-bar' method='get' action='' style='
        display: flex;
        justify-content: center;
        align-items: flex-end;
        justify-content: end;
        margin: 20px 0px;
        gap: 1rem;'>
        <input type='text' name='search' value='$term' class='campo-de-pesquisa'>
        <input type='submit' class='pesquisa' value='Pesquisar'>
        <a href='https://hackone.com.br/hall-da-fama' class='botao-voltar'>X</a>
        </form>
        <li class = 'form-error' style ='display:none;'>O campo precisa conter algum valor</li>
        ";
    }

    return $search_form;
}

function do_a_search($researched, $table_name){

    global $wpdb;

    $researched = str_replace("da", "", $researched);
    $researched = str_replace("dos", "", $researched);
    $researched = str_replace("do", "", $researched);
    $researched = str_replace("Da", "", $researched);
    $researched = str_replace("Dos", "", $researched);
    $researched = str_replace("Do", "", $researched);
    $researched = str_replace("De", "", $researched);
    $researched = str_replace("de", "", $researched);

    
    $expTerm = explode(" ", $researched);

    $has_empity_str = in_array(" ", $expTerm);

    $search = "(";

    foreach($expTerm as $ek=>$ev){

        if($ev !==  " " and $ev !==  ""){
            if($ek == 0 ){
                $search .= "nome LIKE '%".$ev."%' ";
            }else{
                $search .= " OR nome LIKE '%".$ev."%' ";
            }
        }

    }

    $search .= ")";

    $query = "SELECT * FROM $table_name WHERE".$search."ORDER BY pontos DESC";

    $search_results = $wpdb->get_results($query);

    $all_tables_data;

    $table_name_icon = $wpdb->prefix . 'icones_hall_da_fama';

    $positions = $wpdb->prefix . 'positions';

    foreach($search_results as $search_k ){

        $all_icons = "";

        $result = $wpdb->get_row("SELECT * FROM $table_name_icon WHERE email = '$search_k->email' ");
        
        $result_position = $wpdb->get_row("SELECT position FROM $positions WHERE aluno = '$search_k->email' ");

        $index = 0;

        foreach($result as $k => $v){
            if($index >= 2){
                $all_icons .= "<img src='$v' style='max-width: 30px;'>";
            }    
            $index++;
        }
        
        $all_tables_data .= "<tr><td class='classfication'>$result_position->position ??</td><td class='nome'><img src='$search_k->img' alt='foto' style='max-width: 45px; border-radius: 100px;'><h4 class='nome-text'>$search_k->nome</h4></td><td class='pontos'>$search_k->pontos</td><td class= 'icones-conquistas'><div class='icones-container'>$all_icons</div></td><td class='linkedin-h'><a href='$search_k->linkedin' target='_blank'><img src='https://hackone.com.br/wp-content/uploads/2022/10/linkedin.png' style='max-width: 30px; border: none;'></a></td></tr>";
        
    }
    
    return $all_tables_data;

}


function do_a_search_mobile($researched, $table_name){

    global $wpdb;

    $researched = str_replace("da", "", $researched);
    $researched = str_replace("dos", "", $researched);
    $researched = str_replace("do", "", $researched);
    $researched = str_replace("Da", "", $researched);
    $researched = str_replace("Dos", "", $researched);
    $researched = str_replace("Do", "", $researched);
    $researched = str_replace("De", "", $researched);
    $researched = str_replace("de", "", $researched);

    
    $expTerm = explode(" ", $researched);

    $has_empity_str = in_array(" ", $expTerm);

    $search = "(";

    foreach($expTerm as $ek=>$ev){

        if($ev !==  " " and $ev !==  ""){
            if($ek == 0 ){
                $search .= "nome LIKE '%".$ev."%' ";
            }else{
                $search .= " OR nome LIKE '%".$ev."%' ";
            }
        }

    }

    $search .= ")";

    $query = "SELECT * FROM $table_name WHERE".$search."ORDER BY pontos DESC";

    $search_results = $wpdb->get_results($query);

    $all_tables_data;

    $table_name_icon = $wpdb->prefix . 'icones_hall_da_fama';

    $positions = $wpdb->prefix . 'positions';

    foreach($search_results as $search_k ){

        $all_icons = "";

        $result = $wpdb->get_row("SELECT * FROM $table_name_icon WHERE email = '$search_k->email' ");
        
        $result_position = $wpdb->get_row("SELECT position FROM $positions WHERE aluno = '$search_k->email' ");

        $index = 0;

        foreach($result as $k => $v){
            if($index >= 2){
                $all_icons .= "<img src='$v' style='max-width: 30px;'>";
            }    
            $index++;
        }
        
        $all_tables_data .= "   
        <div class='students'>
            <div class='estudantes'>
                <h3 class='ranking'>$result_position->position ??</h3>
                <section class='nome'>
                    <img src='$search_k->img'
                        alt='Foto'>
                    <h3 class='nome-text'>$search_k->nome</h3>
                </section>
            </div>
            <div class='score-conquistas'>
                <div class='title-other'>
                    <div>
                        Score
                    </div>
                    <div>
                        Linkedin
                    </div>
                    <div class='conquistas'>
                        Conquistas
                    </div>
                </div>
                <div class='conquitas-score'>
                    <div class='score'>$search_k->pontos</div>
                    <div class='linkedin'>
                        <a href='$search_k->linkedin' target='_blank'><img src='https://hackone.com.br/wp-content/uploads/2022/10/linkedin.png' style='max-width: 30px; border: none;'></a>
                    </div>
                    <div class='conquistas-itens scroll'>
                        $all_icons
                    </div>
                </div>
            </div>
        </div>
    ";
        
    }
    
    return $all_tables_data;

}

function itens_per_page($table_name){

    global $wpdb;

    $query = "SELECT * FROM $table_name";
    $total_query = "SELECT COUNT(1) FROM (${query}) AS combined_table";
    $total = $wpdb->get_var( $total_query );

    $items_per_page = 100;
    
    $page = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
    
    $offset = ( $page * $items_per_page ) - $items_per_page;

    $result = $wpdb->get_results( $query." ORDER BY pontos DESC LIMIT ${offset}, ${items_per_page}");
    
    return $result;
}

function pagination(){
    global $wpdb;

    $table_name = $wpdb->prefix . 'hall_da_fama_pluggin_version';

    $query = "SELECT * FROM $table_name";
    $total_query = "SELECT COUNT(1) FROM (${query}) AS combined_table";
    $total = $wpdb->get_var( $total_query );

    $items_per_page = 100;
    
    $page = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;

    $page             = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
    $totalPage         = ceil(5);

    if($totalPage > 1){
        $customPagHTML     =  '<div class="pagination">'.paginate_links( array(
        'base' => add_query_arg( 'cpage', '%#%' ),
        'format' => '',
        'prev_text' => __('&laquo;'),
        'next_text' => __('&raquo;'),
        'total' => $totalPage,
        'current' => $page
        )).'</div>';
    }

    return $customPagHTML;
}


function create_icons($google_sheet_data){

    $CCNA =  'https://hackone.com.br/wp-content/uploads/2022/10/CCNA.png';

    $CCNP_ENCOR = "https://hackone.com.br/wp-content/uploads/2022/10/CCNP-ENCOR.png";

    $CCNP_ENARSI = "https://hackone.com.br/wp-content/uploads/2022/10/CCNP-ENARSI.png";

    $SD_WAN = "https://hackone.com.br/wp-content/uploads/2022/10/SD-WAN.png";

    $TROUBLESHOOTING = "https://hackone.com.br/wp-content/uploads/2022/10/TROUBLESHOOTING.png";

    $BGP = "https://hackone.com.br/wp-content/uploads/2022/10/BGP.png";

    $IPV6 = "https://hackone.com.br/wp-content/uploads/2022/10/IMPLEMENTANDO-IPV6.png";

    $MPLS = "https://hackone.com.br/wp-content/uploads/2022/10/MPLS.png";

    $data_center = 'https://hackone.com.br/wp-content/uploads/2022/10/VIRTUALIZACAO-EM-DATA-CENTER.png';

    $marco_zero =  'https://hackone.com.br/wp-content/uploads/2022/10/MARCO-ZERO.png';

    $multicast =  'https://hackone.com.br/wp-content/uploads/2022/10/IMPLEMENTANDO-MULTICAST.png';

    $qos =  'https://hackone.com.br/wp-content/uploads/2022/10/INTRODUCAO-A-QOS.png';
    
    $ospf =  'https://hackone.com.br/wp-content/uploads/2022/10/OSPF.png';

    $eigrp =  'https://hackone.com.br/wp-content/uploads/2022/10/EIGRP.png';

    $wifi =  'https://hackone.com.br/wp-content/uploads/2022/10/IMPLEMENTANDO-WIFI.png';

    $is_is =  'https://hackone.com.br/wp-content/uploads/2022/10/INTRODUCAO-AO-IS-IS.png';

    $service_provider =  'https://hackone.com.br/wp-content/uploads/2022/10/SERVICE-PROVIDER.png';

    $cloud_AWS_Practitioner =  'https://hackone.com.br/wp-content/uploads/2022/10/CLOUD-AWS-PRAC.png';
    
    $cloud_marco_zero =  'https://hackone.com.br/wp-content/uploads/2022/10/CLOUD-MARCO-ZERO.png';
    
    $cloud_virtualizacao =  'https://hackone.com.br/wp-content/uploads/2022/10/CLOUD-VIRTUALIZATION.png';
    
    $aws_zero =  'https://hackone.com.br/wp-content/uploads/2022/10/AWS-ZERO-TO-HERO.png';
    
    $aws_associate =  'https://hackone.com.br/wp-content/uploads/2022/10/AWS-ASSOCIATE.png';
    
    $ciber_marco_zero =  'https://hackone.com.br/wp-content/uploads/2022/10/CIBER-MARCO-ZERO.png';
    
    $nse1 =  'https://hackone.com.br/wp-content/uploads/2022/10/NSE1.png';
    
    $nse2 =  'https://hackone.com.br/wp-content/uploads/2022/10/NSE2.png';
    
    $nse3 =  'https://hackone.com.br/wp-content/uploads/2022/10/NSE3.png';
    
    $nse7 =  'https://hackone.com.br/wp-content/uploads/2022/10/NSE7.png';
    
    $mk_marco_zero =  'https://hackone.com.br/wp-content/uploads/2022/10/MK-MARCO-ZERO.png';
    
    $mk_mtcna =  'https://hackone.com.br/wp-content/uploads/2022/10/MK-MTCNA.png';
    
    $mk_bgp =  'https://hackone.com.br/wp-content/uploads/2022/10/MK-BGP-AVANCADO.png';
    
    $mk_ipv6 =  'https://hackone.com.br/wp-content/uploads/2022/10/MK-IPV6-AVANCADO.png';
    
    $mtcre =  'https://hackone.com.br/wp-content/uploads/2022/10/MTCRE.png';

    $sheets_data = $google_sheet_data;

    
    $sheet_rage = count($sheets_data);

    $icon_per_student = array();
    
    for ($i = 0; $i < $sheet_rage; $i++){

        $current_student = $sheets_data[$i];
        $icons = array();

        $icons["Pontos"] = $current_student["Pontos"];

        if($current_student["CCNA"] != ""){
            $icons["CCNA"] = $CCNA; 
        }  

        if($current_student["CCNP ENCOR"] != ""){
            $icons["CCNP ENCOR"] = $CCNP_ENCOR;
        }

        if($current_student["CCNP ENARSI"] != ""){
            $icons["CCNP ENARSI"] = $CCNP_ENARSI;
        }

        if($current_student["SD-WAN"] != ""){
            $icons["SD-WAN"] = $SD_WAN;
        }

        if($current_student["TROUBLESHOOTING"] != ""){
            $icons["TROUBLESHOOTING"] = $TROUBLESHOOTING;
        }

        if($current_student["BGP"] != ""){
            $icons["BGP"] = $BGP;
        }

        if($current_student["IPV6"] != ""){
            $icons["IPV6"] = $IPV6;
        }

        if($current_student["MPLS E L3VPN"] != ""){
            $icons["MPLS"] = $MPLS;
        }

        if($current_student["Data Center"] != ""){
            $icons["DATA CENTER"] = $data_center;
        }

        if($current_student["Marco Zero - Cisco"] != ""){
            $icons["MARCO ZERO"] = $marco_zero;
        }
        
        if($current_student["Implementando Multicast"] != ""){
            $icons["MULTICAST"] = $multicast;
        }

        if($current_student["QOS"] != ""){
            $icons["QOS"] = $qos;
        }

        if($current_student["OSPF"] != ""){
            $icons["OSPF"] = $ospf;
        }

        if($current_student["EIGRP"] != ""){
            $icons["EIGRP"] = $eigrp;
        }

        if($current_student["WIFI"] != ""){
            $icons["WIFI"] = $wifi; 
        }
        
        if($current_student["IS-IS"] != ""){
            $icons["IS-IS"] = $is_is; 
        }

        if($current_student["SERVICE-PROVIDER"] != ""){
            $icons["SERVICE-PROVIDER"] = $service_provider; 
        }

        if($current_student["Cloud-AWS-Practitioner"] != ""){
            $icons["Cloud-AWS"] = $cloud_AWS_Practitioner; 
        }

        if($current_student["Cloud-Mar-Zero"] != ""){
            $icons["cloud_marco_zero"] = $cloud_marco_zero; 
        }

        if($current_student["Cloud-Virtualiza????o-DC"] != ""){
            $icons["cloud_virtualizacao"] = $cloud_virtualizacao; 
        }    

        if($current_student["AWS Zero to Hero"] != ""){
            $icons["aws_zero"] = $aws_zero; 
        }

        if($current_student["AWS Associate"] != ""){
            $icons["aws_associate"] = $aws_associate; 
        }    

        if($current_student["Ciber Marco 0"] != ""){
            $icons["ciber_marco_zero"] = $ciber_marco_zero; 
        }

        if($current_student["NSE1"] != ""){
            $icons["nse1"] = $nse1; 
        }    

        if($current_student["NSE2"] != ""){
            $icons["nse2"] = $nse2; 
        }

        if($current_student["NSE3"] != ""){
            $icons["nse3"] = $nse3; 
        }

        // if($current_student["NSE4"] != ""){
        //     $icons["nse4"] = $nse4; 
        // }

        if($current_student["NSE7"] != ""){
            $icons["nse7"] = $nse7; 
        }    

        if($current_student["MK-Marco-0"] != ""){
            $icons["mk_marco_zero"] = $mk_marco_zero; 
        }       

        if($current_student["MK-MTCNA"] != ""){
            $icons["mk_mtcna"] = $mk_mtcna; 
        }       

        if($current_student["MK-BGP-AVAN"] != ""){
            $icons["mk_bgp"] = $mk_bgp; 
        }       

        if($current_student["MK-IPv6-AVAN"] != ""){
            $icons["mk_ipv6"] = $mk_ipv6; 
        }       

        if($current_student["MTCRE"] != ""){
            $icons["mtcre"] = $mtcre; 
        }       

        $icon_per_student[$current_student["Email"]] = $icons;
    }

    return $icon_per_student;
    
}

function add_icon_in_db_hall_da_fama($current_student_k, $current_student_v){

    global $wpdb;
    $table_name = $wpdb->prefix . 'icones_hall_da_fama';

    
    $status =  $wpdb->insert(
        $table_name,
        array( 
            // 'ccnp' => $current_student_v["CCNA"], 
            'email' => $current_student_k, 
            'ccnp_enarsi' => $current_student_v["CCNP ENARSI"],
            'ccnp_encor' => $current_student_v['CCNP ENCOR'],
            'ipv6' => $current_student_v['IPV6'],
            'mpls' => $current_student_v['MPLS'],
            'sd_wan' => $current_student_v['SD-WAN'],
            'troubleshooting' => $current_student_v['TROUBLESHOOTING'],
            'bgp'  => $current_student_v['BGP'],
            'data_center' => $current_student_v['DATA CENTER'],
            'marco_zero' => $current_student_v['MARCO ZERO'],
            'multicast' => $current_student_v['MULTICAST'],
            'qos' => $current_student_v['QOS'],
            'ospf' => $current_student_v['OSPF'],
            'eigrp' => $current_student_v['EIGRP'],
            'wifi' => $current_student_v['WIFI'],
            'is_is' => $current_student_v['IS-IS'],
            'service_provider' => $current_student_v['SERVICE-PROVIDER'],
            'ccna' => $current_student_v['CCNA'],
            'cloud_aws' => $current_student_v['Cloud-AWS'],
            'cloud_marco_zero' => $current_student_v['cloud_marco_zero'],
            'cloud_virtualizacao' => $current_student_v['cloud_virtualizacao'],
            'aws_zero' => $current_student_v['aws_zero'],
            'aws_associate' => $current_student_v['aws_associate'],
            'ciber_marco_zero' => $current_student_v['ciber_marco_zero'],
            'nse1' => $current_student_v['nse1'],
            'nse2' => $current_student_v['nse2'],
            'nse3' => $current_student_v['nse3'],
            'nse7' => $current_student_v['nse7'],
            'mk_marco_zero' => $current_student_v['mk_marco_zero'],
            'mk_mtcna' => $current_student_v['mk_mtcna'],
            'mk_bgp' => $current_student_v['mk_bgp'],
            'mk_ipv6' => $current_student_v['mk_ipv6'],
            'mtcre' => $current_student_v['mtcre']
            ) 
        );        
}

function add_all_icon_in_db_hall_da_fama($google_sheet_data){

    $all_students = create_icons($google_sheet_data);

    

    foreach($all_students as $student_k => $student_v){
        add_icon_in_db_hall_da_fama($student_k, $student_v);
    }

    return;
}

function update_all_icon($google_sheet_data){
    global $wpdb;
    $table_name = $wpdb->prefix . 'icones_hall_da_fama';
    $table_name_hall = $wpdb->prefix . 'hall_da_fama_pluggin_version';

    $all_students = create_icons($google_sheet_data);


    foreach($all_students as $student_k => $student_v){

        $result = $wpdb->get_row("SELECT * FROM $table_name WHERE email = '$student_k' ");
        $result_hall = $wpdb->get_row("SELECT * FROM $table_name_hall WHERE email = '$student_k' ");

        if($result and $result_hall->pontos != $student_v["Pontos"]){

            // echo '<pre>';
            // var_dump($result->email);
            // echo '</pre>';

            $status_p = $wpdb->update(
                $table_name,
                array(
                    'ccna' => $student_v['CCNA'],
                    'ccnp_encor' => $student_v['CCNP ENCOR'],
                    'ccnp_enarsi' => $student_v["CCNP ENARSI"],
                    'sd_wan' => $student_v['SD-WAN'],
                    'troubleshooting' => $student_v['TROUBLESHOOTING'],
                    'bgp'  => $student_v['BGP'],
                    'ipv6' => $student_v['IPV6'],
                    'mpls' => $student_v['MPLS'],
                    'data_center' => $student_v['DATA CENTER'],
                    'marco_zero' => $student_v['MARCO ZERO'],
                    'multicast' => $student_v['MULTICAST'],
                    'qos' => $student_v['QOS'],
                    'ospf' => $student_v['OSPF'],
                    'eigrp' => $student_v['EIGRP'],
                    'wifi' => $student_v['WIFI'],
                    'is_is' => $student_v['IS-IS'],
                    'service_provider' => $student_v['SERVICE-PROVIDER'],
                    'cloud_aws' => $student_v['Cloud-AWS'],
                    'cloud_marco_zero' => $student_v['cloud_marco_zero'],
                    'cloud_virtualizacao' => $student_v['cloud_virtualizacao'],
                    'aws_zero' => $student_v['aws_zero'],
                    'aws_associate' => $student_v['aws_associate'],
                    'ciber_marco_zero' => $student_v['ciber_marco_zero'],
                    'nse1' => $student_v['nse1'],
                    'nse2' => $student_v['nse2'],
                    'nse3' => $student_v['nse3'],
                    'nse7' => $student_v['nse7'],
                    'mk_marco_zero' => $student_v['mk_marco_zero'],
                    'mk_mtcna' => $student_v['mk_mtcna'],
                    'mk_bgp' => $student_v['mk_bgp'],
                    'mk_ipv6' => $student_v['mk_ipv6'],
                    'mtcre' => $student_v['mtcre']
                ),
                array(
                    'id' => $result->id
                )
            );
        }
    }
}

function update_positions(){
    global $wpdb;

    $table_name_hall = $wpdb->prefix . 'hall_da_fama_pluggin_version';

    $query = "SELECT * FROM $table_name_hall ORDER BY pontos DESC";

    $results = $wpdb->get_results($query);
    
    $table_name = $wpdb->prefix . 'positions';

    $index = 1;

    foreach($results as $result){

            $status_p = $wpdb->update($table_name, array('aluno' => $result->email, 'position' => $index ), array('id' => $result->id ) );
            $index++;
    }
}

function deactivartion_hall_da_fama(){
    global $wpdb;

    //deleta tabela hall da fama

    $table_name = $wpdb->prefix . 'hall_da_fama_pluggin_version';

    $sql = "DROP TABLE IF EXISTS $table_name";
    
    $wpdb->query($sql);
    
    delete_option("hall_da_fama_pluggin_version");


    // deleta tabela icones

    $table_name_icon = $wpdb->prefix . 'icones_hall_da_fama';

    $sql_icon = "DROP TABLE IF EXISTS $table_name_icon";
    
    $wpdb->query($sql_icon);

    delete_option('icones_hall_da_fama');

    
    // deleta  tabela posicoes

    $table_name_positions = $wpdb->prefix . 'positions';

    $positions = "DROP TABLE IF EXISTS $table_name_positions";
    
    $wpdb->query($positions);

    delete_option('positions');
}

function addlinkedin(){
    $button = "<div class='atualizar-perfil'><a href='https://docs.google.com/forms/d/1C8s1g6qzw02G6bK954ptI_52MGFMmEC82NCKZVZHD1M/viewform?edit_requested=true' class='atualizar-btn' target='_blank'>Atualizar meus dados</a></div>";
    return   $button;
}

add_shortcode( 'hall-da-fama',  'run_all_fuction');
add_shortcode( 'seacher_bar',  'search_bar');
add_shortcode( 'mobile',  'create_html_tables_mobile');
add_shortcode( 'addlinkedin',  'addlinkedin');
add_shortcode( 'pagination',  'pagination');

register_deactivation_hook(__FILE__, 'deactivartion_hall_da_fama' );