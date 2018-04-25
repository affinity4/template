<?php
namespace Affinity4\Template\Exception;

/**
 * --------------------------------------------------
 * File Not Found Exception
 * --------------------------------------------------
 * 
 * @author Luke Watts <luke@affinity4.ie>
 * 
 * @since 1.3.0
 */
class FileNotFoundException extends \Exception
{
    /**
     * --------------------------------------------------
     * Constructor
     * --------------------------------------------------
     * 
     * @author Luke Watts <luke@affinity4.ie>
     * 
     * @since 1.3.0
     */
    public function __construct($message = null, $code = 0, $previous = null)
    {
        if ($message === null) 
        {
            $message = 'A template file could not found.';
        }

        parent::__construct($message, $code, $previous);
    }
}
