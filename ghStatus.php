<?php

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
