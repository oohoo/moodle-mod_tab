// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * *************************************************************************
 * *                         OOHOO - Tab Display                          **
 * *************************************************************************
 * @package
 * @subpackage  tab                                                       **
 * @name        tab                                                       **
 * @copyright   oohoo.biz                                                 **
 * @link        http://oohoo.biz                                          **
 * @author      Patrick Thibaudeau                                        **
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  **
 * *************************************************************************
 * ************************************************************************
 */

define(['jquery'], function($) {
    let modtabjs = {
        obj: null,
        init: function(id) {
            modtabjs.obj = $('#' + id);
            modtabjs.resizeobject();
            window.onresize = function() {
                modtabjs.resizeobject();
            };
        },
        resizeobject: function() {
            let newwidth = $('.tab-content').width();

            modtabjs.obj.css('width', '0px');
            modtabjs.obj.css('height', '0px');

            let newheight = window.visualViewport.height - $('div#page').height() - 40;

            if (newwidth < 600) {
                newwidth = 600;
            }
            if (newheight < 400) {
                newheight = 400;
            }
            modtabjs.obj.css('width', newwidth + 'px');
            modtabjs.obj.css('height', newheight + 'px');
        }
    };

    return modtabjs;
});
