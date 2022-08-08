<?php 

/**
 * Plugin Name:       Hall da Fama
 * Plugin URI:        https://https://github.com/rodrigowolfgang47
 * Description:       Esse plugin consome apis do google sheets.
 * Version:           0.1.3
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

    if(false === get_option( 'hall_da_fama_pluggin_version' )){
        create_database_table();
        add_new_stundents();
        return;
    }

    update_db();
    add_new_stundents();
    delite_non_stundents();

    create_icons();

    return create_html_tables();

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
		email text NOT NULL,
		pontos int NOT NULL,
		linkedin text NULL,
		PRIMARY KEY  (id)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
    
    add_option( "hall_da_fama_pluggin_version", $hall_da_fama_pluggin_version );
}


function is_email_in_db($query_search){

    // Você pode consultar os campos das tavelas aqui
    // Campos recomendados são e-mail ou nome

    global $wpdb;
            
    $table_name = $wpdb->prefix . 'hall_da_fama_pluggin_version';
    
    $query = "SELECT email FROM $table_name WHERE email = '$query_search' ";

    $result = $wpdb->get_results($query);

    if(count($result) > 0){
        return true;
    }

    return false;

}

function add_new_stundents(){

    $google_sheet_data = get_goooglesheet_data();
    
    $sheet_rage = count($google_sheet_data);
    
    $is_all_there = false;
    
    for ($i = 0; $i < $sheet_rage; $i++){
        
        $current_student = $google_sheet_data[$i];
        
        $current_email = $google_sheet_data[$i]['Email'];
        
        $is_student_in_db = is_email_in_db($current_email);
        
        if(false === $is_student_in_db){
            add_value_in_db($current_student);
        }      
    }
    
    return;

}

function add_value_in_db($current_student){
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'hall_da_fama_pluggin_version';

    
    $status =  $wpdb->insert(
        $table_name,
        array( 
            'time' => current_time( 'mysql' ), 
            'nome' =>  $current_student["Nome Completo"], 
            'email' => $current_student["Email"], 
            'pontos' => intval($current_student["Pontos"]), 
            'linkedin' => $current_student["Linkedin"], 
            ) 
        );
        
}

function update_db(){
    
    global $wpdb;

    $google_sheet_data = get_goooglesheet_data();

    $sheet_rage = count($google_sheet_data);

    $table_name = $wpdb->prefix . 'hall_da_fama_pluggin_version';

    for ($i = 0; $i < $sheet_rage; $i++){

        $email = $google_sheet_data[$i]['Email'];
        $current_points = intval($google_sheet_data[$i]['Pontos']);
        $current_name = $google_sheet_data[$i]['Nome Completo'];

        $results = $wpdb->get_row("SELECT * FROM $table_name WHERE email = '$email' ");

        if ($results) {
            // Upadate data        
            $status_p = $wpdb->update($table_name, array('pontos' => $current_points), array('id' => $results->id));
            $status_n = $wpdb->update($table_name, array('nome' => $current_name), array('id' => $results->id));

        }
                    
    }
     
}

function create_html_tables(){

    $term = $_GET['search'];

    $table_title = "<table class='main_table'><tr class='table-title' ><td>Nome</td><td>Pontos</td><td>Linkedin</td><td>Conquistas</td></tr>";

    global $wpdb;                

    $table_name = $wpdb->prefix . 'hall_da_fama_pluggin_version';  
                    
    $table_info = itens_per_page($table_name);

    $icones_array = create_icons();

    $all_tables_data;

    if(isset($term)){
        $all_tables_data = do_a_search($term, $table_name);

    }else{
        foreach($table_info as $info){

            $current_student_icon = $icones_array[$info->email];
            $all_icons = "";

            foreach($current_student_icon as $icon){
                $all_icons .= "<img src='$icon' style='max-width: 30px;'>";
            }
            
            $all_tables_data .= "<tr><td class='nome'>$info->nome</td><td class='pontos'>$info->pontos</td><td><img src='https://sandbox.ccielucaspalma.com.br/wp-content/uploads/2022/08/linkedin.png' style='max-width: 30px; border: none;'></td><td>$all_icons</tr>";
        }
    }


    $complete_table = "$table_title  $all_tables_data </table>";

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

function search_bar(){
    $search_form = "
    <form method='get' action='' style='
    display: flex;
    justify-content: center;
    align-items: flex-end;
    justify-content: end;
    margin: 20px 0px;
    gap: 1rem;'>
    <input type='text' name='search' class='campo-de-pesquisa'  >
    <input class='pesquisa' type='submit' value='Pesquisar'>
    </form>
    ";

    $term = $_GET['search'];

    if(isset($_GET['search'])){
        $search_form = "
        <form method='get' action='' style='
        display: flex;
        justify-content: center;
        align-items: flex-end;
        justify-content: end;
        margin: 20px 0px;
        gap: 1rem;'>
        <input type='text' name='search' value='$term' class='campo-de-pesquisa'>
        <input type='submit' class='pesquisa' value='Pesquisar'>
        <a href='https://sandbox.ccielucaspalma.com.br/hall-da-fama-layout' class='botao-voltar'>X</a>
        </form>
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

    $query = "SELECT * FROM $table_name WHERE".$search."ORDER BY nome ASC";

    $search_results = $wpdb->get_results($query);

    $all_tables_data;

    $icones_array = create_icons();

    foreach($search_results as $search_k ){

            $current_student_icon = $icones_array[$search_k->email];
            $all_icons = "";

            foreach($current_student_icon as $icon){
                $all_icons .= "<img src='$icon' style='max-width: 30px;'>";
            }
        
        $all_tables_data .= "<tr><td class='nome'>$search_k->nome</td><td class='pontos'>$search_k->pontos</td><td><img src='https://sandbox.ccielucaspalma.com.br/wp-content/uploads/2022/08/linkedin.png' style='max-width: 30px; border: none;'></td><td>$all_icons</tr>";
        
    }
    
    return $all_tables_data;

}

function itens_per_page($table_name){

    global $wpdb;

    $query = "SELECT * FROM $table_name";
    $total_query = "SELECT COUNT(1) FROM (${query}) AS combined_table";
    $total = $wpdb->get_var( $total_query );

    $items_per_page = 10;
    
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

    $items_per_page = 10;
    
    $page = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;

    $page             = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
    $totalPage         = ceil($total / $items_per_page);

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


function create_icons(){

    $CCNP_ENARSI = "https://sandbox.ccielucaspalma.com.br/wp-content/uploads/2022/08/CCNP-ENARSI.png";

    $CCNP_ENCOR = "https://sandbox.ccielucaspalma.com.br/wp-content/uploads/2022/08/CCNP-ENCOR.png";

    $IPV6 = "https://sandbox.ccielucaspalma.com.br/wp-content/uploads/2022/08/IPV6.png";

    $MPLS = "https://sandbox.ccielucaspalma.com.br/wp-content/uploads/2022/08/MPLS.png";

    $SD_WAN = "https://sandbox.ccielucaspalma.com.br/wp-content/uploads/2022/08/SD-WAN.png";

    $TROUBLESHOOTING = "https://sandbox.ccielucaspalma.com.br/wp-content/uploads/2022/08/TROUBLESHOOTING.png";

    $BGP = "https://sandbox.ccielucaspalma.com.br/wp-content/uploads/2022/08/BGP.png";

    $sheets_data = get_goooglesheet_data();

    
    $sheet_rage = count($sheets_data);

    $icon_per_student = array();
    
    for ($i = 0; $i < $sheet_rage; $i++){

        $current_student = $sheets_data[$i];
        $icons = array();


        if($current_student["CCNP ENCOR"] != ""){
            array_push($icons, $CCNP_ENCOR);
        }

        if($current_student["CCNP ENARSI"] != ""){
            array_push($icons, $CCNP_ENARSI);
        }

        if($current_student["SD-WAN"] != ""){
            array_push($icons, $SD_WAN);
        }

        if($current_student["TROUBLESHOOTING"] != ""){
            array_push($icons, $TROUBLESHOOTING);
        }

        if($current_student["BGP"] != ""){
            array_push($icons, $BGP);
        }

        if($current_student["IPV6"] != ""){
            array_push($icons, $IPV6);
        }

        if($current_student["MPLS E L3VPN"] != ""){
            array_push($icons, $MPLS);
        }
        $icon_per_student[$current_student["Email"]] = $icons;
    }

    return $icon_per_student;
    
}



add_shortcode( 'hall-da-fama',  'run_all_fuction');
add_shortcode( 'seacher_bar',  'search_bar');
add_shortcode( 'pagination',  'pagination');
