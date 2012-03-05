<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Console\Helper;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * The Progress class providers helpers to display progress output.
 *
 * @author Chris Jones <leeked@gmail.com>
 */
class ProgressHelper extends Helper
{
    const FORMAT_QUIET   = '%percent%%';
    const FORMAT_NORMAL  = '%current%/%max% [%bar%] %percent%%';
    const FORMAT_VERBOSE = '%current%/%max% [%bar%] %percent%% Elapsed: %elapsed%';

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Current step
     *
     * @var int
     */
    private $current;

    /**
     * Maximum number of steps
     *
     * @var int
     */
    private $max;

    /**
     * Have we started the progress bar?
     *
     * @var int
     */
    private $started = false;

    /**
     * List of formatting variables
     *
     * @var array
     */
    protected $defaultFormatVars = array(
        'current',
        'max',
        'bar',
        'percent',
        'elapsed',
    );

    /**
     * Available formatting variables
     *
     * @var array
     */
    protected $formatVars;

    /**
     * Stored format part widths
     *
     * @var array
     */
    protected $widths;

    /**
     * Various time formats
     */
    private $timeFormats = array(
        array(0, '???'),
        array(1, '1 sec'),
        array(59, 'secs', 1),
        array(60, '1 min'),
        array(3600, 'mins', 60),
        array(5400, '1 hr'),
        array(86400, 'hrs', 3600),
        array(129600, '1 day'),
        array(604800, 'days', 86400),
        array(907200, '1 week'),
        array(2628000, 'weeks', 604800),
        array(3942000, '1 month'),
        array(31536000, 'months', 2628000),
        array(47304000, '1 year'),
        array(3153600000, 'years', 31536000),
    );

    /**
     * @var array
     */
    protected $options = array(
        'barWidth'     => 28,
        'barChar'      => '=',
        'emptyBarChar' => '-',
        'progressChar' => '>',
        'format'       => self::FORMAT_NORMAL,
        'redrawFreq'   => 1,
    );

    /**
     * Starts the progress output.
     *
     * @param OutputInterface $output  An Output instance
     * @param integer         $max     Maximum steps
     * @param array           $options Options for progress helper
     */
    public function start(OutputInterface $output, $max = null, array $options = array())
    {
        $this->started = time();
        $this->current = 0;
        $this->max     = (int) $max;
        $this->output  = $output;

        switch ($output->getVerbosity()) {
            case OutputInterface::VERBOSITY_QUIET:
                $this->options['format'] = self::FORMAT_QUIET;
                break;
            case OutputInterface::VERBOSITY_VERBOSE:
                $this->options['format'] = self::FORMAT_VERBOSE;
                break;
        }

        $this->options = array_merge($this->options, $options);
        $this->inititalize();
    }

    /**
     * Initialize the progress helper.
     */
    protected function inititalize()
    {
        $this->formatVars = array();
        foreach ($this->defaultFormatVars as $var) {
            if (strpos($this->options['format'], "%{$var}%") !== false) {
                $this->formatVars[] = $var;
            }
        }

        $this->widths = array();
        if (in_array('current', $this->formatVars)) {
            $this->widths['current'] = strlen($this->max);
        }

        if (in_array('percent', $this->formatVars)) {
            $this->widths['percent'] = 3;
        }

        if ($this->max <= 0) {
            $this->options['barChar'] = $this->options['emptyBarChar'];
        }
    }

    /**
     * Advances the progress output X steps.
     *
     * @param integer $step   Number of steps to advance
     * @param Boolean $redraw Whether to redraw or not
     */
    public function advance($step = 1, $redraw = true)
    {
        $this->current += $step;
        if ($redraw && $this->current % $this->options['redrawFreq'] === 0) {
            $this->display();
        }
    }

    /**
     * Finish the progress output
     */
    public function finish()
    {
        $this->started = false;
        $this->output  = null;
    }

    /**
     * Generates the array map of format variables to values.
     *
     * @return array Array of format vars and values
     */
    protected function generate()
    {
        $vars = array();

        $percent = 0;
        if ($this->max > 0) {
            $percent = (double) $this->current / $this->max;
        }

        if (in_array('bar', $this->formatVars)) {
            $completeBars = 0;
            $emptyBars    = 0;
            if ($this->max > 0) {
                $completeBars = floor($percent * $this->options['barWidth']);
            } else {
                $completeBars = floor($this->current % $this->options['barWidth']);
            }

            $emptyBars = $this->options['barWidth'] - $completeBars - strlen($this->options['progressChar']);
            $bar = str_repeat($this->options['barChar'], $completeBars);
            if ($completeBars < $this->options['barWidth']) {
                $bar .= $this->options['progressChar'];
                $bar .= str_repeat($this->options['emptyBarChar'], $emptyBars);
            }

            $vars['bar'] = $bar;
        }

        if (in_array('elapsed', $this->formatVars)) {
            $elapsedSecs = time() - $this->started;
            $vars['elapsed'] = $this->humaneTime($elapsedSecs);
        }

        if (in_array('current', $this->formatVars)) {
            $vars['current'] = str_pad($this->current, $this->widths['current'], ' ', STR_PAD_LEFT);
        }

        if (in_array('max', $this->formatVars)) {
            $vars['max'] = $this->max;
        }

        if (in_array('percent', $this->formatVars)) {
            $vars['percent'] = str_pad($percent * 100, $this->widths['percent'], ' ', STR_PAD_LEFT);
        }

        return $vars;
    }

    /**
     * Outputs the current progress string .
     */
    public function display()
    {
        $message = $this->options['format'];
        foreach ($this->generate() as $name => $value) {
            $message = str_replace("%{$name}%", $value, $message);
        }
        $this->overwrite($this->output, $message);
    }

    /**
     * Converts seconds into human-readable format.
     *
     * @param integer $secs Number of seconds
     *
     * @return string Time in readable format
     */
    protected function humaneTime($secs)
    {
        $text = '';
        foreach ($this->timeFormats as $format) {
            if ($secs < $format[0]) {
                if (count($format) == 2) {
                    $text = $format[1];
                    break;
                } else {
                    $text = ceil($secs / $format[2]) . ' ' . $format[1];
                    break;
                }
            }
        }
        return $text;
    }

    /**
     * Overwrites a previous message to the output.
     *
     * @param OutputInterface $output   An Output instance
     * @param string|array    $messages The message as an array of lines or a single string
     * @param Boolean         $newline  Whether to add a newline or not
     * @param integer         $size     The size of line
     */
    protected function overwrite(OutputInterface $output, $messages, $newline = true, $size = 80)
    {
        for ($place = $size; $place > 0; $place--) {
            $output->write("\x08", false);
        }

        $output->write($messages, false);

        for ($place = ($size - strlen($messages)); $place > 0; $place--) {
            $output->write(' ', false);
        }

        // clean up the end line
        for ($place = ($size - strlen($messages)); $place > 0; $place--) {
            $output->write("\x08", false);
        }

        if ($newline) {
            $output->write('');
        }
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'progress';
    }
}
