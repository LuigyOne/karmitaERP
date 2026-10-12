<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Inicio sesión &mdash; <?= !empty(get_compnay_title()) ? get_compnay_title() : 'Taskhub'; ?></title>
  <?php include('include-css.php'); ?>
</head>

<body>
  <div id="app">
    <section class="section">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
            <div class="login-brand">
              <img src="<?= base_url('assets/icons/' . (!empty(get_full_logo()) ? get_full_logo() : 'logo.png')); ?>" alt="logo" width="350">
            </div>

            <div class="card card-primary">
              <div class="card-header">
                <h4>Inicio de Sesión</h4>
              </div>

              <div class="card-body">

                <?= form_open('auth/login', 'id="loginpage"'); ?>
                <div class="form-group">
                  <label for="email">Email</label>
                  <?= form_input(['name' => 'identity', 'placeholder' => 'Email', 'class' => 'form-control']) ?>
                  <div class="invalid-feedback">
                    Por favor ingrese su email
                  </div>
                </div>

                <div class="form-group">
                  <div class="d-block">
                    <label for="password" class="control-label">Contraseña</label>
                    <div class="float-right">
                      <a href="auth/forgot" class="text-small">
                        ¿Olvidó su contraseña?
                      </a>
                    </div>
                  </div>

                  <?= form_password(['name' => 'password', 'placeholder' => 'Password', 'class' => 'form-control']) ?>
                  <div class="invalid-feedback">
                    Por favor ingrese su contraseña
                  </div>
                </div>
                <div class="form-group">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                    <label class="form-check-label">
                      Mantenme conectado
                    </label>
                  </div>
                </div>
                <div id="login-result" class="alert alert-info d-none">
                </div>
                <div class="form-group">
                  <button type="submit" id="loginbtn" class="btn btn-primary btn-lg btn-block" tabindex="4">
                    Iniciar Sesión
                  </button>
                </div>
                </form>
              </div>
            </div>
            <div class="simple-footer">
              Copyright &copy; <?= get_compnay_title(); ?> <?= date('Y'); ?>
              <br>
              Desarrollado By <a href="" target="_blank">digitalNEURON</a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <script>
    /* These must be here, We can not move this inside an external script, since its values are being read from PHP variables */
    csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
    csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
  </script>

  <?php include('include-js.php'); ?>

</body>

</html>