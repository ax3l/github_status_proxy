<?php

/** Copyright 2014 Axel Huebl
 *
 *  This file is part of github_status_proxy.
 *
 *  github_status_proxy is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  github_status_proxy is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with github_status_proxy. If not, see <http://www.gnu.org/licenses/>.
 */

/** Debug settings */
if( config::debug == TRUE )
{
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

/** Error-Log a message
 *
 * \param $message as string
 * \param $always  log the message even if \see config::debug is FALSE
 */
function debugLog( string $message, boolean $always = false )
{
    if( $always || config::debug )
        error_log( "GitHub Status Proxy: " . $message );
}

?>
