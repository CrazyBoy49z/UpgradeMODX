<?php
/**
* Resolver to connect widgets to system events for UpgradeModx extra
*
* Copyright 2015 by Bob Ray <http://bobsguides.com>
* Created on 08-14-2015
*
 * UpgradeModx is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * UpgradeModx is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * UpgradeModx; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
* @package upgrademodx
* @subpackage build
*/
/* @var $object xPDOObject */
/* @var $widgetObj modDashboardWidget */
/* @var xPDOObject $object */
/* @var array $options */
/* @var $modx modX */
/* @var $widgetObj modDashboardWidget */
/* @var $widgetPlacement modDashboardWidgetPlacement */
/* @var $dashboard modDashboard */

if (!function_exists('checkFields')) {
    function checkFields($required, $objectFields) {

        global $modx;
        $fields = explode(',', $required);
        foreach ($fields as $field) {
            if (!isset($objectFields[$field])) {
                $modx->log(modX::LOG_LEVEL_ERROR, '[Widget Resolver] Missing field: ' . $field);
                return false;
            }
        }
        return true;
    }
}


if ($object->xpdo) {
    $modx =& $object->xpdo;
    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
        case xPDOTransport::ACTION_UPGRADE:



            $intersects = array (
                0 =>  array (
                  'widget' => 'UpgradeModx',
                  'dashboard' => 1,
                  'rank' => 0,
                ),
            );

            if (is_array($intersects)) {
                foreach ($intersects as $k => $fields) {
                    /* make sure we have all fields */
                    if (!checkFields('widget,dashboard', $fields)) {
                        continue;
                    }
                    $widget = $modx->getObject('modDashboardWidget', array('name' => $fields['widget']));

                    $dashboard = $modx->getObject('modDashboard', (int) $fields['dashboard']);

                    if (!$widget || !$dashboard) {
                        if (!$widget) {
                            $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find Widget  ' .
                                $fields['widget']);
                        }
                        if (!$dashboard) {
                            $modx->log(xPDO::LOG_LEVEL_ERROR, 'Could not find dashboard with ID ' .
                                $fields['dashboard']);
                        }
                        continue;
                    }
                    $widgetPlacement = $modx->getObject('modDashboardWidgetPlacement',
                        array(
                            'widget'=>$widget->get('id'),
                            'dashboard' => (int)$fields['dashboard'],
                            )
                    );
                    
                    if (!$widgetPlacement) {
                        $widgetPlacement = $modx->newObject('modDashboardWidgetPlacement');
                    }
                    if ($widgetPlacement) {
                        $fields['rank'] = isset($fields['rank']) ? (int) $fields['rank'] : 0;
                        $widgetPlacement->set('widget', (int) $widget->get('id'));
                        $widgetPlacement->set('dashboard', (int) $fields['dashboard']);
                        $widgetPlacement->set('rank', (int)$fields['dashboard']);

                    }
                    if (! $widgetPlacement->save()) {
                        $modx->log(xPDO::LOG_LEVEL_ERROR, 'Unknown error saving widgetPlacement for ' .
                            $fields['widget'] . ' - ' . $fields['event']);
                    }
                }
            }
            break;

        case xPDOTransport::ACTION_UNINSTALL:
            foreach($newEvents as $k => $fields) {
                $event = $modx->getObject('modEvent', array('name' => $fields['name']));
                if ($event) {
                    $event->remove();
                }
            }
            break;
    }
}

return true;