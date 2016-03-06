<?php
function ConsoleLog( $data ) {
    if ( is_array( $data ) )
        $output = "<script>console.log( 'Log : " . implode( ',', $data) . "' );</script>";
    else
        $output = "<script>console.log( 'Log : " . $data . "' );</script>";
	echo($output);
}
?>