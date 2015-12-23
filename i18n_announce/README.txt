
** Description

The i18n_announce plugin allows you to display short messages
are shown for a specific period of time on your website.
This is useful for announcements, such upcoming events.
The plugin supports use of more than one language as
configured by the i18n_base plugin available for GetSimple.

** Installation

The i18n_announce plugin requires the i18n_base plugin,
ensure it is installed before installing i18n_announce.
Unzip the files in the zip file in the GetSimple' plugins folder
and active the plugin. 

** Usage

The announcements can be added under 'Pages'/'Announcements'.
To show the announcements on the site you can use 
the following code in the template:

<?php 
    if (current_announcement_exists()) { 
        get_current_announcements();
    }
?>

The announcements don't have any markup except for a <br/> when 
multiple announcements are shown.


** Known issues

The following will be addressed in a coming release:
   * Delete announcement
   * Input fields are too small


** Changelog

 Version 0.1
   Initial release. 

** License

 The i18n_annouce plugin is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 The i18_announce plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
