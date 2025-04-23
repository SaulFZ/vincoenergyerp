<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <title>Vinco</title>
    <link rel="canonical" href="https://codepen.io/Gibbu/pen/qxRwRp" />
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"
        integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
    <script defer src="https://use.fontawesome.com/releases/v5.0.1/js/all.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.transit/0.9.12/jquery.transit.js"
        integrity="sha256-mkdmXjMvBcpAyyFNCVdbwg4v+ycJho65QLDwVE3ViDs=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/5.0.0/normalize.min.css" />

    <style>
        * {
            outline-width: 0;
            font-family: "Montserrat" !important;
        }

        /* Estilos generales */
        body {
            font-family: var(--fuente-principal);
            margin: 0;
            padding: 0;
            background: url('/img/fondo.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        #cont {
            height: 100vh;
            background-size: cover !important;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        #invContainer {
            display: flex;
            overflow: hidden;
            position: relative;
            border-radius: 5px;
        }

        #invContainer .acptContainer {
            padding: 45px 30px;
            box-sizing: border-box;
            width: 400px;
            margin-left: -400px;
            overflow: hidden;
            height: 0;
            opacity: 0;
        }

        #invContainer .acptContainer.loadIn {
            opacity: 1;
            margin-left: 0;
            transition: 0.5s ease;
        }

        #invContainer .acptContainer:before {
            content: "";
            background-size: cover !important;
            box-shadow: inset 0 0 0 3000px rgba(40, 43, 48, 0.75);
            filter: blur(10px);
            position: absolute;
            width: 150%;
            height: 150%;
            top: -50px;
            left: -50px;
        }

        form {
            position: relative;
            text-align: center;
            height: 100%;
        }

        form h1 {
            margin: 0 0 15px 0;
            font-family: "Work Sans" !important;
            font-weight: 700;
            font-size: 20px;
            color: #fff;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            opacity: 0;
            left: -30px;
            position: relative;
            transition: 0.5s ease;
        }

        form h1.loadIn {
            left: 0;
            opacity: 1;
        }

        .frmContainer {
            text-align: left;
        }

        .frmContainer .frmDiv {
            margin-bottom: 30px;
            left: -25px;
            opacity: 0;
            transition: 0.5s ease;
            position: relative;
        }

        .frmContainer .frmDiv.loadIn {
            opacity: 1;
            left: 0;
        }

        .frmContainer .frmDiv:last-child {
            margin-bottom: 0;
        }


        .frmContainer p {
            margin: 0;
            font-weight: 700;
            color: #aaa;
            font-size: 10px;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        .frmContainer input[type="password"],
        .frmContainer input[type="text"] {
            background: transparent;
            border: none;
            box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.15);
            padding: 15px 0;
            box-sizing: border-box;
            color: #fff;
            width: 100%;
        }

        .frmContainer .frmDiv p {
            position: relative;
        }

        .frmContainer .frmDiv p i {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
        }

        .frmContainer input[type="password"],
        .frmContainer input[type="text"] {
            padding-left: 40px;
        }

        .frmContainer .fa-user,
        .frmContainer .fa-lock {
            position: absolute;
            top: 25px;
            left: 10px;
            color: #ffffff;
            font-size: 18px;
        }

        .logoCont {
            padding: 45px 35px;
            box-sizing: border-box;
            position: relative;
            z-index: 2;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            transform: scale(0, 0);
        }

        .logoCont img {
            width: 150px;
            margin-bottom: -5px;
            display: block;
            position: relative;
        }

        .logoCont img:first-child {
            width: 150px;
        }

        .logoCont .text {
            padding: 25px 0 10px 0;
            margin-top: -70px;
            opacity: 0;
        }

        .logoCont .text.loadIn {
            margin-top: 0;
            opacity: 1;
            transition: 0.8s ease;
        }

        .logoCont .logo {
            position: relative;
            top: -20px;
            opacity: 0;
        }

        .logoCont .logo.loadIn {
            top: 0;
            opacity: 1;
            transition: 0.8s ease;
        }

        .logoCont:before {
            content: "";
            background-size: cover !important;
            position: absolute;
            top: -50px;
            left: -50px;
            width: 150%;
            height: 150%;
            filter: blur(10px);
            box-shadow: inset 0 0 0 3000px rgb(255, 255, 255);
        }

        .frgtPas {
            color: #aaa;
            opacity: 0.8;
            text-decoration: none;
            font-weight: 700;
            font-size: 10px;
            margin-top: 15px;
            display: block;
            transition: 0.2s ease;
        }

        .frgtPas:hover {
            opacity: 1;
            color: #fff;
        }

        .acptBtn {
            margin-top: 20px;
            width: 100%;
            box-sizing: border-box;
            background: #d67e29;
            border: none;
            color: #fff;
            padding: 20px 0;
            border-radius: 3px;
            cursor: pointer;
            transition: 0.2s ease;
            user-select: none;
        }

        .acptBtn:hover {
            background: #ff7b00ef;
        }

        .register {
            color: #aaa;
            font-size: 12px;
            padding-top: 15px;
            display: block;
        }

        .register a {
            color: #fff;
            text-decoration: none;
            margin-left: 5px;
            box-shadow: inset 0 -2px 0 transparent;
            padding-bottom: 5px;
            user-select: none;
        }

        .register a:hover {
            box-shadow: inset 0 -2px 0 #fff;
        }
    </style>

</head>

<body translate="no">

    <div id="cont">
        <div id="invContainer">
            <div class="logoCont">
                <img class="logo" src="img/logo.png" /><img />
            </div>
            <div class="acptContainer">
                <form>
                    <h1>Bienvenido!</h1>
                    <div class="frmContainer">
                        <div class="frmDiv" style="transition-delay: 0.2s">
                            <i class="fas fa-user"></i>
                            <p>Usuario</p>
                            <input type="text" required />
                        </div>
                        <div class="frmDiv" style="transition-delay: 0.4s">
                            <i class="fas fa-lock"></i>
                            <p>Contraseña</p>
                            <input type="password" required />
                            <div class="frmDiv" style="transition-delay: 0.6s">
                                <button class="acptBtn" type="button"
                                    onclick="window.location.href='/view/index.html'">Login</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // JQUERY
            $(function() {
                var images = [];

                $("#cont").append(
                    "<style>#cont, .acptContainer:before, .logoCont:before {background: url(" +
                    images[Math.floor(Math.random() * images.length)] +
                    ") center fixed }"
                );

                setTimeout(function() {
                    $(".logoCont").transition({
                        scale: 1
                    }, 700, "ease");
                    setTimeout(function() {
                        $(".logoCont .logo").addClass("loadIn");
                        setTimeout(function() {
                            $(".logoCont .text").addClass("loadIn");
                            setTimeout(function() {
                                $(".acptContainer").transition({
                                    height: "431.5px"
                                });
                                setTimeout(function() {
                                    $(".acptContainer").addClass("loadIn");
                                    setTimeout(function() {
                                        $(".frmDiv, form h1").addClass("loadIn");
                                    }, 500);
                                }, 500);
                            }, 500);
                        }, 500);
                    }, 1000);
                }, 10);
            });
        </script>
</body>

</html>
