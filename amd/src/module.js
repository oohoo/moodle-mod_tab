/**
 * *************************************************************************
 * *                         OOHOO - Tab Display                          **
 * *************************************************************************
 * @package     mod                                                       **
 * @subpackage  tab                                                       **
 * @name        tab                                                       **
 * @copyright   oohoo.biz                                                 **
 * @link        http://oohoo.biz                                          **
 * @author      Patrick Thibaudeau                                        **
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  **
 * *************************************************************************
 * ************************************************************************ */
define(['jquery'], function($) {
    var modtabjs = {
        obj: null,
        init: function(id) {
            modtabjs.obj = $('#' + id);
            modtabjs.resizeobject();
            window.onresize = function () {
                modtabjs.resizeobject();
            };
        },
        resizeobject: function() {
            var newwidth = $('.tab-content').width();

            modtabjs.obj.css('width', '0px');
            modtabjs.obj.css('height', '0px');

            var newheight = window.visualViewport.height - $('div#page').height() - 40;

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
