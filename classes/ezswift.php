<?php

include_once( 'lib/ezutils/classes/ezini.php' );

class eZSwift
{
    /*!
     \static
     \brief returns a Swift connection instance based on the MailSettings in site.ini
    */
    function getConnection( $emailSender = false )
    {
        $ini = eZINI::instance();
        $transportType = trim( $ini->variable( 'MailSettings', 'Transport' ) );

        $connection = false;
        if ( $transportType == 'sendmail' )
        {
            // Swift has special sendmail connection class Swift_Connection_Sendmail
            // but also one using the PHP mail function() called Swift_Connection_NativeMail
            // eZ Publish mail library uses PHP mail function when transport is set to "sendmail"
            // so we will do this as well for Swift
            $sendmailOptionsArray = $ini->variable( 'MailSettings', 'SendmailOptions' );
            $sendmailOptions = implode( ' ', $sendmailOptionsArray );

            $isSafeMode = ini_get( 'safe_mode' ) != 0;
            if ( !$isSafeMode and $emailSender )
            {
                $sendmailOptions .= ' -f'. $emailSender;
            }

            require_once 'extension/ezswift/swift/lib/Swift/Connection/NativeMail.php';
            $connection = new Swift_Connection_NativeMail( $sendmailOptions );
        }
        elseif ( $transportType == 'smtp' )
        {
            $host = trim( $ini->variable( 'MailSettings', 'TransportServer' ) );
            $helo = $ini->hasVariable( 'MailSettings', 'SenderHost' ) ? trim( $ini->variable( 'MailSettings', 'SenderHost' ) ) : 'localhost';
            $port = trim( $ini->variable( 'MailSettings', 'TransportPort' ) );
            $user = trim( $ini->variable( 'MailSettings', 'TransportUser' ) );
            $password = trim( $ini->variable( 'MailSettings', 'TransportPassword' ) );

            if ( $port === '' )
            {
                $port = null;
            }

            require_once 'extension/ezswift/swift/lib/Swift/Connection/SMTP.php';
            $connection = new Swift_Connection_SMTP( $host, $port );

            if ( trim( $user ) != '' && trim( $password ) != '' )
            {
                $connection->setUsername( $user );
                $connection->setPassword( $password );
            }
        }

        return $connection;
    }
}

?>