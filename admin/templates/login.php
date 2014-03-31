<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" >
    <head>
        <title>{{ TITLE_PAGE }}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/vnd.microsoft.icon" href="img/favicon.ico"/>
        <link href="{{ app.request.basepath }}/css/font/font.css" rel="stylesheet" type="text/css" />
        <link href="{{ app.request.basepath }}/css/main.css" rel="stylesheet" type="text/css" />
        <link href="{{ app.request.basepath }}/css/bootstrap.css" rel="stylesheet" type="text/css" />
        <link href="{{ app.request.basepath }}/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
        <link href="{{ app.request.basepath }}/css/bootstrap-responsive.css" rel="stylesheet" type="text/css" />
        <link href="{{ app.request.basepath }}/css/pepper-grinder/jquery-ui-1.10.3.custom.min.css" rel="stylesheet" type="text/css" />
        <style>
            body { background-color: #F5F5F5; padding-bottom: 40px; padding-top: 40px; }
        </style>

    </head>
    <body id="bodyappfb" >
        <div class="container">
                    
            <form id="login-form" class="form-signin">
                <h2 class="form-signin-heading">Por favor, inicie sesión</h2>
                <input type="text" id="inputUser" placeholder="User"  name="user" class="input-block-level" >
                <input type="password" id="inputPassword" placeholder="Password" name="password" class="input-block-level" >
                <button type="submit" class="btn btn-large btn-primary" >Iniciar sesión <i class="icon-chevron-right icon-white"></i></button>
            </form>

       </div>

        <script src="{{ app.request.basepath }}/js/jquery-1.9.1.js"></script>
        <script src="{{ app.request.basepath }}/js/bootstrap.js"></script>
        <script src="{{ app.request.basepath }}/js/bootstrap-datetimepicker.min.js"></script>
        <script src="{{ app.request.basepath }}/js/jquery-ui-1.10.3.custom.js"></script>
        <script src="{{ app.request.basepath }}/js/login.form.js"></script>
        
        <script type="text/javascript">
            var basepath = '{{ app.request.basepath }}';
            jQuery(function(){

                jQuery('#login-form').loginform({
                    'error_message':'Usuario o contraseña incorrecto'
                });

            });
        </script>

    </body>
</html>
