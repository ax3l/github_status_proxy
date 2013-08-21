<?php

require_once('config.php');

/** ...
 */
class ipRange
{
    /** Check if an IP is in a certain net mask range
     *
     * @param ip      ip to check for
     * @param ipRange as array of {ip, mask} arrays
     * @return boolean true if in at least one of the ranges supplied
     */
    public static function test( $ip )
    {
        // IPv6
        if( substr_count( $ip, ":" ) > 0 &&
            substr_count( $ip, "." ) == 0 )
        {
            // todo, but GitHub only uses IPv4 right now
            return false;
        }
        // IPv4
        else
        {
            foreach( config::$github_iprange as $value )
            {
                if( self::ip_in_network( $ip, $value['ip'], $value['mask'] ) )
                    return true;
            }
        }
        return false;
    }

    /** Check if a IPv4 adress is in a certain range
     * 
     * With kind permission of Jeremy Wadhams (php.net)
     *
     * @param ip       ip as ipv4 adress string
     * @param net_addr ipv4 adress to check against
     * @param net_mast corresponding net_mask
     * @return         boolean true or false
     */
    private static function
    ip_in_network( $ip, $net_addr, $net_mask )
    {
        if( $net_mask <= 0 )
            return false;
        $ip_binary_string  = sprintf( "%032b", ip2long( $ip ) );
        $net_binary_string = sprintf( "%032b", ip2long( $net_addr ) );
        return ( substr_compare( $ip_binary_string, $net_binary_string, 0,
                                 $net_mask )  === 0);
    }

} // class ipRange

?>
