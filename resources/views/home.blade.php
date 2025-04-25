<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Vinco Energy</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-color: #f5f5f5;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: #fff;
            padding-top: 20px;
        }

        .sidebar .logo {
            text-align: center;
            padding: 15px 0;
            border-bottom: 1px solid #3d556c;
        }

        .sidebar .logo img {
            width: 120px;
        }

        .sidebar ul {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar ul li {
            padding: 12px 20px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .sidebar ul li:hover {
            background-color: #3d556c;
        }

        .sidebar ul li.active {
            background-color: #d67e29;
        }

        .sidebar ul li i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .content {
            flex: 1;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 24px;
            color: #333;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-info img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .user-info .name {
            font-weight: 500;
        }

        .user-info .dropdown {
            margin-left: 10px;
            cursor: pointer;
        }

        .dashboard {
            margin-top: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .dashboard h2 {
            margin-bottom: 20px;
            color: #333;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
        }

        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
        }

        .stat-card.orange .icon {
            background-color: #ffe0c3;
            color: #d67e29;
        }

        .stat-card.blue .icon {
            background-color: #c3dfff;
            color: #2980b9;
        }

        .stat-card.green .icon {
            background-color: #c3ffe0;
            color: #27ae60;
        }

        .stat-card.purple .icon {
            background-color: #e0c3ff;
            color: #8e44ad;
        }

        .stat-card .data h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .stat-card .data p {
            color: #777;
            font-size: 14px;
        }

        .logout-btn {
            color: #fff;
            text-decoration: none;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 20px;
            margin-left: 20px;
            margin-right: 20px;
            background-color: #e74c3c;
            border-radius: 5px;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        .logout-btn i {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar">
            <div class="logo">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Vinco Logo">
            </div>
            <ul>
                <li class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</li>
                <li><i class="fas fa-chart-line"></i> Estadísticas</li>
                <li><i class="fas fa-users"></i> Usuarios</li>
                <li><i class="fas fa-cog"></i> Configuración</li>
                <li><i class="fas fa-question-circle"></i> Ayuda</li>
            </ul>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </button>
            </form>
        </div>

        <div class="content">
            <div class="header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <img src="{{ asset('assets/img/user.png') }}" alt="User">
                    <div>
                        <div class="name">{{ session('auth_user')['name'] }}</div>
                        <div class="role">{{ session('auth_user')['role'] }}</div>
                    </div>
                    <div class="dropdown">
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </div>

            <div class="dashboard">
                <h2>Bienvenido a Vinco Energy ERP</h2>

                <div class="stats">
                    <div class="stat-card orange">
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="data">
                            <h3>$24,500</h3>
                            <p>Ingresos</p>
                        </div>
                    </div>

                    <div class="stat-card blue">
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="data">
                            <h3>145</h3>
                            <p>Ventas</p>
                        </div>
                    </div>

                    <div class="stat-card green">
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="data">
                            <h3>250</h3>
                            <p>Clientes</p>
                        </div>
                    </div>

                    <div class="stat-card purple">
                        <div class="icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="data">
                            <h3>15</h3>
                            <p>Proyectos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
