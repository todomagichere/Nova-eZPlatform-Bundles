/*
 * NovaeZMenuManagerBundle.
 *
 * @package   NovaeZMenuManagerBundle
 *
 * @author    florian
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaeZMenuManagerBundle/blob/master/LICENSE
 */

const path = require('path')

module.exports = (Encore) => {
  Encore.addEntry('ezplatform-admin-ui-modules-menu-manager-js', [
    path.resolve(__dirname, '../../../modules/menu-manager/menu.manager.renderer.js')
  ])

  Encore.addEntry('ezplatform-admin-ui-modules-menu-manager-css', [
    path.resolve(__dirname, '../public/css/open-iconic-bootstrap.min.css'),
    path.resolve(__dirname, '../public/css/menu-manager.css')
  ])

}
