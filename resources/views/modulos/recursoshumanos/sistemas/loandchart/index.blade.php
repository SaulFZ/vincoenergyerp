<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
  <meta charset="UTF-8" />
  <title>VincoLoadChart</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!--FULLCALENDAR-->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/fullcalendar.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.4.0/locale/es.js"></script>

  <!--OTRO CALENDAR-->

  <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>



  <link href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

  <!-- FIREBASE -->
  <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-firestore.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-storage.js"></script>
  <!--<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>-->
  <link rel="stylesheet" href="/css/styles.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
  <div class="sidebar">
    <div class="logo-details">
      <i class="bx bx-menu" id="btn"></i>
    </div>
    <div class="logo-icono">
      <img src="/img/logo.png" alt="VincoLoadChart" />
    </div>
    <div class="logo-details">
      <div class="logo_name">Load Chart</div>
    </div>
    <li>
      <a href="index.html" class="active">
        <i class="fas fa-user-tie"></i>
        <span class="links_name">Gerencial</span>
      </a>
      <span class="tooltip">Gerencial</span>
    </li>
    <li>
      <a href="administracion.html" class="nav-link" data-page="administracion.html">
        <i class="fas fa-network-wired"></i>
        <span class="links_name">Administración</span>
      </a>
      <span class="tooltip">Administración</span>
    </li>
    <li>
      <a href="qhse.html" class="nav-link" data-page="qhse.html">
        <i class="fas fa-users-gear"></i>

        <span class="links_name">QHES</span>
      </a>
      <span class="tooltip">QHES</span>
    </li>
    <li>
      <a href="ventas.html" class data-page="ventas.html">
        <i class="fas fa-chart-line"></i>
        <span class="links_name">Ventas</span>
      </a>
      <span class="tooltip">Ventas</span>
    </li>
    <li>
      <a href="operaciones.html" class="nav-link" data-page="operaciones.html">
        <i class="fas fa-hard-hat"></i>
        <span class="links_name">Operaciones</span>
      </a>
      <span class="tooltip">Operaciones</span>
    </li>
    <li>
      <a href="laboratorio.html" class="nav-link" data-page="laboratorio.html">
        <i class="fas fa-tools"></i>
        <span class="links_name">Laboratorio</span>
      </a>
      <span class="tooltip">Laboratorio</span>
    </li>
    <li>
      <a href="ingenierosdecampo.html" class="nav-link" data-page="ingenierosdecampo.html">
        <i class="fas fa-hard-hat"></i>
        <span class="links_name">Ingenieros de Campo</span>
      </a>
      <span class="tooltip">Ingenieros de Campo</span>
    </li>
    <li>
      <a href="suministro.html" class="nav-link" data-page="suministro.html">
        <i class="fas fa-dolly"></i>
        <span class="links_name">Suministro</span>
      </a>
      <span class="tooltip">Suministro</span>
    </li>

    <li>
      <a href="geociencias.html" class="nav-link" data-page="geociencias.html">
        <i class="fas fa-globe-americas"></i>
        <span class="links_name">Geociencias</span>
      </a>
      <span class="tooltip">Geociencias</span>
    </li>
    <li class="log_out">
      <div class="profile_log_out">
        <div class="log_out_content">
          <div class="name">Cerrar Sesión</div>
        </div>
      </div>
      <i class="bx bx-log-out" id="log_out"></i>
    </li>
  </div>

  <section class="home-section">

    <header class="header">
      <div class="content">
        <i class="fas fa-user-circle"></i>
        <div class=" user-info">
          <h1 class="greeting" id="username">¡Hola, Administrador!</h1>
          <p class=" subtitle ">Bienvenido al panel de Gerencial</p>
        </div>
      </div>
      <nav class="navigation">
        <ul>
          <li>
            <a href="empleados.html " class=" nav-empleados " data-page="empleados.html">
              <i class="fas fa-user-friends"></i>
              <span class="name">Empleados Vinco</span>
            </a>
          </li>
        </ul>
      </nav>
      </nav>
    </header>

    <div class="date-general">
      <h2>Datos Generales</h2>
      <div class=" info-cards ">
        <div class=" info-card warn ">
          <i class=" fas fa-users "></i>
          <div class=" card-content ">
            <div class=" dashboardwidget-value ">15</div>
            <hr>
            <div class=" dashboardwidget-title ">Personal de Gerencial</div>
          </div>
        </div>

        <div class=" info-card pass ">
          <i class=" fas fa-users "></i>
          <div class=" card-content ">
            <div class=" dashboardwidget-value ">100</span></div>
            <hr>
            <div class=" dashboardwidget-title ">Personal de Vinco</div>
          </div>
        </div>
      </div>
    </div>

    <div class="letter-meanings-container">
      <h2>Informacion</h2>
      <table class="letter-meanings">
        <tbody>
          <tr>
            <td class="T">T</td>
            <td class="T">Trabajo en Base</td>
            <td class="P">P</td>
            <td class="P">Trabajo en Pozo</td>
            <td class="M">M</td>
            <td class="M">Trabajo en Marina</td>
            <td class="D">D</td>
            <td class="D">Descanso</td>
          </tr>
          <tr>
            <td class="V">V</td>
            <td class="V">Vacaciones</td>
            <td class="C">C</td>
            <td class="C">Curso o Capacitación</td>
            <td class="F">F</td>
            <td class="F">Falta</td>
            <td class="S">S</td>
            <td class="S">Suspensión Disciplinaria</td>
          </tr>
          <tr>
            <td class="I">I</td>
            <td class="I">Incapacidad</td>
            <td class="PG">PG</td>
            <td class="PG">Permiso con Goce de Sueldo</td>
            <td class="PSG">PSG</td>
            <td class="PSG">Permiso sin Goce de Sueldo</td>
            <td class="HO">HO</td>
            <td class="HO">Home Office</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="container">
      <h3 class="titulo-tabla">Load Chart</h3>
      <div id="containerBtnAcciones">
        <button class="btn-actividad" id="tabla_actividades_dia">Actividad <i class="fas fa-chart-line"></i></button>
        <button id="show-calendar-btn">Mostrar Calendario <i class="fas fa-calendar-alt"></i></button>
      </div>

      <div id="calendar-container">
        <button id="close-btn">X</button>
        <div id="calendar"></div>
        <div id="floating-form">
          <H2>Dias de las Quincenas</H2>
          <div class="input-container">
            <label for="event-title">Nombre del Evento:</label>
            <select id="event-title">
              <option value="">Seleccione un título</option>
              <option value="ENERO">ENERO</option>
              <option value="FEBRERO">FEBRERO</option>
              <option value="MARZO">MARZO</option>
              <option value="ABRIL">ABRIL</option>
              <option value="MAYO">MAYO</option>
              <option value="JUNIO">JUNIO</option>
              <option value="JULIO">JULIO</option>
              <option value="AGOSTO">AGOSTO</option>
              <option value="SEPTIEMBRE">SEPTIEMBRE</option>
              <option value="OCTUBRE">OCTUBRE</option>
              <option value="NOVIEMBRE">NOVIEMBRE</option>
              <option value="DICIEMBRE">DICIEMBRE</option>
            </select>
            <div class="title-validation validation-alert">
              <p>Por favor, ingrese un título.</p>
            </div>
          </div>
          <div class="input-container">
            <label for="event-color">Color:</label>
            <select id="event-color">
              <option value="">Seleccione</option>
              <option value="naranja">Naranja</option>
              <option value="azul">Azul</option>
            </select>
            <div class="color-validation validation-alert">
              <p>Por favor, seleccione un color.</p>
            </div>
          </div>
          <div class="input-container">
            <label for="event-start">Fecha de Inicio:</label>
            <input type="text" id="event-start" placeholder="Fecha de inicio">
          </div>
          <div class="input-container">
            <label for="event-middle">Fin de la primera quincena:</label>
            <input type="text" id="event-middle" placeholder="Fecha fin quincena 1">
          </div>
          <div class="input-container">
            <label for="event-end">Fecha de Fin:</label>
            <input type="text" id="event-end" placeholder="Fecha de fin">
          </div>
          <div class="input-container">
            <input type="submit" id="submit-event" value="Guardar Evento">
            <button id="update-event" style="display: none;">Actualizar Evento</button>
            <button id="cancel-event">Cancelar</button>
            <button id="delete-event" style="display: none;">Eliminar Evento</button>
          </div>
        </div>
      </div>

      <div class="table-container">
        <table class="user-data">
          <thead class="user-thead">
            <tr class="tr_user">
              <th>No.</th>
              <th>Clave</th>
              <th>Nombre</th>
              <th>Puesto</th>
              <th>Departamento</th>
              <th>Ingreso </th>
              <th class="T">T</th>
              <th class="P">P</th>
              <th class="M">M</th>
              <th class="D">D</th>
              <th class="V">V</th>
              <th class="C">C</th>
              <th class="F">F</th>
              <th class="S">S</th>
              <th class="I">I</th>
              <th class="PG">PG</th>
              <th class="PSG">PSG</th>
              <th class="HO">HO</th>
              <th>Totales</th>
              <th>Totales</th>
              <th class="vacation">Vacaciones</th>
              <th class="vacation">Descanso</th>
              <th class="vacation">Utilización</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <div class="cont-flotante" id="formulario_flotante" style="display: none;">
    <!-- CAMBIO DE HML DE LOS DIV QUINCENA -->
    <div class="quincenas-container">
      <div id="div-quincena1" class="quin1">
        <div class="color-indicator"></div>
      </div>
      <div id="div-quincena2" class="quin2">
        <div class="color-indicator"></div>
      </div>
    </div>

      <button class="btn-cerrar" id="cerrar_formulario">Cerrar</button>
      <div class="cont-tablas">

      <table class="data-user">
        <thead class="thead-user">
          <tr id="thead-user" class="user_tr">
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
    </div>
    <div class="container-guardar">
      <button class="guardar-btn" id="guardar_formulario">Guardar</button>
    </div>
  </div>

  <script>

    document.querySelectorAll('.celda-editable').forEach(function (elemento) {
      moverCursorAlFinal(elemento);
    });

    function validarSiglas(elemento) {
      const siglasPermitidas = ["T", "P", "M", "D", "V", "C", "F", "S", "I", "PG", "PSG", "HO", "N"];
      const colores = {
        'T': '#005C9A',
        'P': '#06a1de',
        'M': '#028E36',
        'D': '#CCC610',
        'V': '#C69C00',
        'C': '#AEAEAE',
        'F': '#C40203',
        'S': '#A30000',
        'I': '#9A7400',
        'PG': '#C2C2C2',
        'PSG': '#CB8F64',
        'HO': '#839AC0',
        'N': '#b5b5b5',
      };

      // Manejar el evento de entrada de texto
      elemento.addEventListener('input', function (event) {
        // Obtener el nuevo contenido de la celda
        let nuevoContenido = elemento.innerText.trim().toUpperCase();

        // Verificar si el nuevo contenido es una de las siglas permitidas
        const esNuevoValido = siglasPermitidas.includes(nuevoContenido);

        // Si el nuevo contenido no es válido, no hacer cambios
        if (!esNuevoValido) {
          return;
        }

        // Aplicar el estilo correspondiente
        elemento.style.backgroundColor = colores[nuevoContenido] || '';
        elemento.style.color = 'white';

        // Mover el cursor al final del texto
        moverCursorAlFinal(elemento);
      });
    }



    function moverCursorAlFinal(elemento) {
      var range = document.createRange();
      var sel = window.getSelection();
      range.selectNodeContents(elemento);
      range.collapse(false);
      sel.removeAllRanges();
      sel.addRange(range);
      elemento.focus();
    }

    function limitarDosCifras(elemento) {
      // Obtener el contenido de la celda
      var contenido = elemento.textContent.trim();

      // Eliminar caracteres no numéricos
      contenido = contenido.replace(/\D/g, '');

      // Limitar la longitud a dos cifras
      contenido = contenido.substring(0, 2);

      // Actualizar el contenido de la celda
      elemento.textContent = contenido;

      // Mover el cursor al final del texto
      moverCursorAlFinal(elemento);
    }

    function limitarCuatroCifras(elemento) {
      // Obtener el contenido de la celda
      var contenido = elemento.textContent.trim();

      // Eliminar caracteres no numéricos
      contenido = contenido.replace(/\D/g, '');

      // Limitar la longitud a cuatro cifras
      contenido = contenido.substring(0, 4);

      // Actualizar el contenido de la celda
      elemento.textContent = contenido;

      // Mover el cursor al final del texto
      moverCursorAlFinal(elemento);
    }

  </script>

  <script src="../js/script.js"></script>

  <script type="module" src="../model/index.js"></script>
</body>

</html>
