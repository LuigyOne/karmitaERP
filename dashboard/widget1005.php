<?php
/**********************************************************************
	Copyright (C) NotrinosERP.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
	See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
/**
 * CRM Dashboard Widget 1005 — Contenedor Iframe para Mapa Geolocalizado.
 */

$widget = new Widget();
$widget->setTitle(_('Geolocalización (Clientes, Vendedores y Bodegas)'));
$widget->Start();

// Verificamos permisos estándar de CRM o de visualización
if ($widget->checkSecurity('SA_CRM_PIPELINE')) {
    // Contenedor iframe con altura optimizada (720px) para evitar recortes
    echo "<div style='width: 100%; height: 720px; overflow: hidden; margin: 0; padding: 0;'>";
    echo "    <iframe src='../crm/map_view.php' style='width: 100%; height: 100%; border: none;' sandbox='allow-same-origin allow-scripts allow-forms allow-popups'></iframe>";
    echo "</div>";
} else {
    display_note(_('No tiene permisos para ver este módulo de geolocalización.'));
}

$widget->End();
