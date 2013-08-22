<?php

/** Copyright 2013 Axel Huebl
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

class ghStatus
{
    /** Waiting for test client */
    const pending = 'pending';
    /** Test client returned a success */
    const success = 'success';
    /** Test client errored internally */
    const error   = 'error';
    /** Test client returned test failed */
    const failure = 'failure';

} // class ghStatus

class ghType
{
    /** Standard commit via push */
    const commit = 'commit';
    /** Pull request */
    const pull   = 'pull';

} // class ghType

?>
