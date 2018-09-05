  <div class="piep-login" <?php if(is_user_logged_in() || isset($_GET['forgotpwd'])) { echo "style='display:none'"; } ?> >
      <div class="piep-login__menu">
        <a href="" class='active' data-section='login'>LOGIN</a>
        <a href=""  data-section='registo'>REGISTAR</a>
        <a href="" data-section='forgot'>ESQUECI</a>
      </div>
      <div class="piep-login__content">
          <div class='piep-login__content-elem elem-nome'>
            <label for="email">Nome:</label>
            <input type="text" id="nome" name="nome" placeholder="Nome"/>  </div>
          <div class='piep-login__content-elem elem-email active'>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="email" pattern=".+@.+\..+"/></div>
          <div class='piep-login__content-elem elem-password active'>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="password" /></div>
      </div>
      <div class="piep-login__submit">
        <input type="checkbox" name="gpdr" id="gpdr" required>
        <p>GPDR info <a href="">more info</a> stuff</p>
        <a href="#" id='submitme' class='submit'>LOGIN</a>
      </div>
    </div>
    
    <?php if(!is_user_logged_in() && isset($_GET['forgotpwd'])) { ?>
      <div class="piep-login" >
      <div class="piep-login__content">
          <div class='piep-login__content-elem elem-email active'>
            <label for="email">Email:</label>
            <input type="email" id="emailforgot" name="email" placeholder="email" pattern=".+@.+\..+"/></div>
          <div class='piep-login__content-elem elem-password active'>
            <label for="password">Password:</label>
            <input type="password" id="passwordforgot" name="password" placeholder="nova password" /></div>
      </div>
      <div class="piep-login__submit">
        <a href="#" id='submitforgot' class='submit'>Alterar Password</a>
      </div>
    </div>
    <?php } ?>
    
<script>
  <?php echo "const nonce = '" . wp_create_nonce( 'clientepage' . date("l") ) . "'"; ?> 
</script>
<script type="text/javascript">
  const ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
