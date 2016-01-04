<?php
namespace Craft;

/**
 * AmSearch - Debug service
 */
class AmSearch_DebugService extends BaseApplicationComponent
{
    private $_debug = false;
    private $_messages = array();

    private $_timer = false;
    private $_timerStart = 0;
    private $_globalTimer = false;
    private $_globalTimerStart = 0;

    /**
     * Get debug messages.
     *
     * @param bool $output
     *
     * @return mixed
     */
    public function getMessages($output = false)
    {
        if (! $this->_debug) {
            return null;
        }

        if ($output) {
            echo '<pre>'.print_r($this->_messages, true).'</pre>';
        }
        else {
            return $this->_messages;
        }
    }

    /**
     * Add a debug message.
     *
     * @param bool $globalTimer
     *
     * @param string $message
     */
    public function addMessage($message, $globalTimer = false)
    {
        if ($globalTimer) {
            if ($this->_globalTimer) {
                $this->_globalTimer = false;
                $message .= sprintf(' (Execution time: %s seconds)',
                    round(microtime(true) - $this->_globalTimerStart, 2)
                );
            }
        }
        else {
            if ($this->_timer) {
                $this->_timer = false;
                $message .= sprintf(' (Execution time: %s seconds)',
                    round(microtime(true) - $this->_timerStart, 2)
                );
            }
        }
        $this->_messages[] = $message;
    }

    /**
     * Start a timer to calculate the execution time.
     *
     * @param bool $globalTimer
     */
    public function startTimer($globalTimer = false)
    {
        if ($globalTimer) {
            $this->_globalTimer = true;
            $this->_globalTimerStart = microtime(true);
        }
        else {
            $this->_timer = true;
            $this->_timerStart = microtime(true);
        }
    }

    /**
     * Stop the timer and return the result.
     *
     * @param bool $globalTimer
     *
     * @return bool|float
     */
    public function stopTimer($globalTimer = false)
    {
        if ($globalTimer) {
            if ($this->_globalTimer) {
                $this->_globalTimer = false;
                return round(microtime(true) - $this->_globalTimerStart, 2);
            }
        }
        else {
            if ($this->_timer) {
                $this->_timer = false;
                return round(microtime(true) - $this->_timerStart, 2);
            }
        }
    }
}
