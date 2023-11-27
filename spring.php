<?php
/**
 * Plugin Name: Mostrar Tabla de Grupos desde Spring Boot
 * Plugin URI: https://github.com/AndresMaitaMedina/pluginsWP/edit/main/Springboot%20local
 * Description: Muestra una tabla de grupos desde tu aplicación Spring Boot en WordPress.
 * Version: 1.0.0
 * Author: Tu Nombre
 * Author URI: https://github.com/AndresMaitaMedina/pluginsWP
 * License: GPL2
 */

// Agrega un shortcode llamado "mostrar_tabla_grupos".
add_shortcode('mostrar_tabla_grupos', 'mostrar_tabla_grupos_func');

// Función que se ejecutará cuando se llame al shortcode.
function mostrar_tabla_grupos_func()
{
    // Número de registros por bloque.
    $registros_por_bloque = 30;

    // Página actual (por defecto es 1).
    $pagina_actual = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;

    // Calcula el índice de inicio para la página actual.
    $indice_inicio = ($pagina_actual - 1) * $registros_por_bloque;

    // URL de tu aplicación Spring Boot para obtener datos de grupos.
    $api_url = 'http://localhost:8081/Grupos/GruposPendientes';

    // Realiza una solicitud GET a tu aplicación Spring Boot.
    $response = wp_remote_get($api_url);

    // Verifica si la solicitud fue exitosa.
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        // Convierte la respuesta JSON a un array asociativo.
        $data = json_decode(wp_remote_retrieve_body($response), true);

        // Verifica si hay datos antes de construir la tabla.
        if (!empty($data) && is_array($data)) {
            // Obtener los registros para la página actual.
            $data_pagina_actual = array_slice($data, $indice_inicio, $registros_por_bloque);

            // Construir la tabla con los datos de grupos.
            $table_html = '<style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                    text-align: center; /* Añadido para centrar el contenido */
                }
                th, td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: center; /* Añadido para centrar el contenido */
                }
                th {
                    background-color: #33AFFF;
                }
                .pagination {
                    margin-top: 10px;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                }
                .pagination a {
                    padding: 8px;
                    margin: 0 5px;
                    border: 1px solid #ddd;
                    text-decoration: none;
                    color: #333;
                    background-color: #f8f8f8;
                    border-radius: 4px;
                }
                .pagination a:hover {
                    background-color: #ddd;
                }
            </style>';

            $table_html .= '<table>';
            $table_html .= '<thead><tr><th>ID</th><th>Código</th><th>Fecha Inicio</th><th>Fecha Fin</th><th>Vacantes</th><th>Vacantes Disponibles</th><th>DNI</th><th>Profesor</th><th>Nivel</th></tr></thead>';

            foreach ($data_pagina_actual as $grupo) {
                $table_html .= '<tr>';
                $table_html .= '<td>' . esc_html($grupo['id']) . '</td>';
                $table_html .= '<td>' . esc_html($grupo['cod']) . '</td>';
                $table_html .= '<td>' . esc_html($grupo['fchinicio']) . '</td>';
                $table_html .= '<td>' . esc_html($grupo['fchfin']) . '</td>';
                $table_html .= '<td>' . esc_html($grupo['vacantes']) . '</td>';
                $table_html .= '<td>' . esc_html($grupo['vacantesdisp']) . '</td>';
                $table_html .= '<td>' . esc_html($grupo['provcdocumento']) . '</td>';
                $table_html .= '<td>' . esc_html($grupo['provcnombre'] . ' ' . $grupo['provcapellido']) . '</td>';
                $table_html .= '<td>' . esc_html($grupo['nivelnombre']) . '</td>';
                $table_html .= '</tr>';
            }

            $table_html .= '</table>';

            // Agregar enlaces de paginación.
            if (count($data) > ($indice_inicio + $registros_por_bloque)) {
                $table_html .= '<div class="pagination">';
                $table_html .= '<a href="' . esc_url(add_query_arg('pagina', $pagina_actual + 1)) . '">Cargar más</a>';
                $table_html .= '</div>';
            }

            return $table_html;
        } else {
            return 'No hay datos de grupos disponibles.';
        }
    } else {
        // Manejar el caso en que la solicitud no fue exitosa.
        return 'Error al consultar la API de Spring Boot.';
    }
}
