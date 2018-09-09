<?php

//code to add to functions (ajaxa call)
add_action('wp_ajax_my_dologin', 'my_dologin');
add_action('wp_ajax_nopriv_my_dologin', 'my_dologin');
function my_dologin(){
        $result= array();
        $nonce = $_POST['nonce'];
        //try to hack?
        //if all the params are sent and user is not logged in
        if ( is_user_logged_in() || !isset($_POST['section']) ||  !wp_verify_nonce( $nonce,  'clientepage' . date("l")) ) {
          //que é que tas a fazer?
          $result['error'] = 'olhós avioens látraz';
          echo json_encode($result);
          die();
        }
        //get post params
        $cburl = null;
        $nome = null;
        $pwd = null;
        $email = null;
        $section = null;
        $key = null;
        $value = null;
        //verificado em cima
        $section = $_POST['section']; 
        if(isset($_POST['email'])) { $email = $_POST['email']; }
        if(isset($_POST['nome'])) { $nome = $_POST['nome']; }
        if(isset($_POST['password'])) { $pwd = $_POST['password']; }
        if(isset($_POST['cburl'])) { $cburl = $_POST['cburl']; }
        if(isset($_POST['key'])) { $key = $_POST['key']; }
        if(isset($_POST['value'])) { $value = $_POST['value']; }
        // ############################################ LOGIN ################################
        if($section=='login') {
          
          //não existe email ou username
          if(!email_exists($email)) { 
            $result['error'] = 'Não existe esse username';
            echo json_encode($result);
            die(); 
          }
          $creds = array( 'user_login'    => $email, 'user_password' => $pwd, 'remember'  => true );
          $user_verify = wp_signon( $creds,  is_ssl()  );
          //catch error
          if( is_wp_error( $user_verify ) ) {
              // Show the error
              //echo "<pre>".print_r($user_id,true)."</pre>";
              if( strpos( $user_verify->get_error_message(), 'A senha que introduziu para o endereço de email' ) !== false ) {
                $result['error'] = "Erro de login: password errada.";
              }
              else {  $result['error'] = $user_verify->get_error_message(); }
              echo json_encode($result);
              die();
          } 
          $user = get_user_by( 'id', $user_verify->ID ); 
          //authenticate
          if( $user ) {
              wp_set_current_user( $user_verify->ID, $user->user_login );
              wp_set_auth_cookie( $user_verify->ID );
              do_action( 'wp_login', $user->user_login );
              //redirect if callback url is set 
              if($cburl && get_permalink($cburl)) {  $result['relink'] = get_permalink($cburl); }
              $result['success'] = true;
              //echo "<pre>".print_r($user_verify,true)."</pre>";
              echo json_encode($result);
              die();
          }
        }
        // ################################## REGISTO ##############################################
        if($section == 'registo') {
          if(!email_exists($email)) { 
            //this sanitizes the fields so no need to do it
            $user_id = wp_insert_user(array('user_login' => $email, 'user_email' => $email, 'display_name' => $nome, 'first_name' => $nome, 'user_pass' => $pwd ));
            if( is_wp_error( $user_id ) ) {
              // Show the error
              //echo "<pre>".print_r($user_id,true)."</pre>";
              $result['error'] = $user_verify->get_error_message(); 
              
              echo json_encode($result);
              die();
            } 
            //registo feito com sucesso
            update_user_meta( $user_id, 'show_admin_bar_front', 'false' ); 
            if($cburl && get_permalink($cburl)) {  $result['relink'] = get_permalink($cburl); }
            //fazer login
            $creds = array(
              'user_login'    => $email, 'user_password' => $pwd,  'remember' => true );
            $user_verify = wp_signon( $creds,  is_ssl()  );
            wp_set_current_user( $user_verify->ID, $user->user_login );
            wp_set_auth_cookie( $user_verify->ID );
            do_action( 'wp_login', $user->user_login );
            $result['success'] = true;
            echo json_encode($result);
            die();
          }
          //username já registado
          else {
            $result['error'] = "Esse username ($email) já está registado.";
            echo json_encode($result);
            die();
          }
        }
         //############################### FORGOT PASSWORD ###############################################
        if($section == 'forgot') {
            //forgot password
            $forget = my_forget_pwd($email);
            if($forget) {
              //manda ver ao mail
              $result['success'] = "Nova password gerada com sucesso, por favor veja o seu email.";
              echo json_encode($result);
              die();
            }
            else {
              if($forget == -1) $result['error'] = "Falha no envio do mail";
              if($forget == 0) $result['error'] = "Esse utilizador não existe";
              echo json_encode($result);
              die();
            }
        }
        //########################### NEW PASSWORD #########################################################
        if($section == 'forgotpwd') {

          $user_data = get_user_by( 'email', $email ); 
          $result['user_data'] = $user_data;
          if(!$user_data || !($value == $user_data->ID) || !check_password_reset_key($key,$email)) { 
              $result['error'] = "Ocorreu algum erro. Contacte um administrador.";
              echo json_encode($result);
              die();}
          //LOGIN E Reload
          else {
            wp_set_password( $pwd, $value );
            update_user_meta($value, 'user_activation_key', '');
            $creds = array( 'user_login'    => $email, 'user_password' => $pwd, 'remember'  => true );
            $user_verify = wp_signon( $creds,  is_ssl()  );
            wp_set_current_user( $user_verify->ID, $user->user_login );
            wp_set_auth_cookie( $user_verify->ID );
            do_action( 'wp_login', $user->user_login );
            $result['success'] = true;
             $result['relink'] = get_permalink(11);
            echo json_encode($result);
            die();
          }
        }
       
        //END DOLOGIN
        }
       

 //## forgot password function ###################################################
function my_forget_pwd($email) {
  global $wpdb; 
  $user_data = get_user_by( 'email', $email ); 
  if(!$user_data) { return 0; }
  //dados
  $user_id = $user_data->ID;
  $user_name = $user_data->display_name;
  $user_email = $user_data->user_email;
  //gerar key
  $key = wp_generate_password(20, false);
  $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_email));
  //check for lang
  $message = __('Someone requested that the password be reset for the following account:') . "<br><br><br>";
  $message .= get_option('siteurl') . "<br><br>";
  $message .= sprintf(__('Username: %s'), $user_email) . "<br><br><br>";
  $message .= __('If this was a error, just ignore this email as no action will be taken.') . "<br><br>";
  $message .= __('To reset your password, visit the following address:') . "<br><br>";
  $message .= '<a href="'.esc_url(get_permalink( 11 )) . '?forgotpwd=true&key='.$key.'&value=' .$user_id . '" >'.esc_url(get_permalink( 11 )) . '?forgotpwd=true&key='.$key.'&value=' .$user_id . '</a><br><br>';

  if ( $message && !wp_mail($user_email, ' Password Reset Request', $message) ) { return -1 ; }
  else return true;
}

?>
