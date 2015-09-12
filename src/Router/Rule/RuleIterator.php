<?php namespace buildr\Router\Rule; 

use \Iterator;
use buildr\Router\Rule\RuleInterface;

/**
 * Rule Iterator
 *
 * BuildR PHP Framework
 *
 * @author Zoltán Borsos <zolli07@gmail.com>
 * @package buildr
 * @subpackage Router\Rule
 *
 * @copyright    Copyright 2015, Zoltán Borsos.
 * @license      https://github.com/Zolli/BuildR/blob/master/LICENSE.md
 * @link         https://github.com/Zolli/BuildR
 */
class RuleIterator implements Iterator {

    /**
     * @type array
     */
    protected $rules = [];

    /**
     * Constructor
     *
     * @param array $rules
     */
    public function __construct(array $rules = []) {
        $this->rules = $rules;
    }

    /**
     * Set the rules to iterate through
     *
     * @param array $rules
     */
    public function set(array $rules) {
        $this->rules = [];

        foreach($rules as $rule) {
            $this->append($rule);
        }
    }

    /**
     * Appends a new rule to the stack
     *
     * @param callable $rule
     */
    public function append(callable $rule) {
        $this->rules[] = $rule;
    }

    /**
     * Prepends a new rule to the stack
     *
     * @param callable $rule
     */
    public function prepend(callable $rule) {
        array_unshift($this->rules, $rule);
    }

    /**
     * Returns the current rule
     *
     * @return \buildr\Router\Rule\RuleInterface
     */
    public function current() {
        $rule = current($this->rules);

        if($rule instanceof RuleInterface) {
            return $rule;
        }

        $key = key($this->rules);
        $factory = $this->rules[$key];
        $rule = $factory();

        if($rule instanceof RuleInterface) {
            $this->rules[$key] = $rule;
            return $rule;
        }

        $message = gettype($rule);
        $message .= ($message != 'object') ?: ' of type ' . get_class($rule);
        $message = "Expected RuleInterface, got {$message} for key {$key}";
        throw new \UnexpectedValueException($message);
    }

    /**
     * Returns the current rule key
     *
     * @return mixed
     */
    public function key() {
        return key($this->rules);
    }

    /**
     * Moves the iterator pointer to the next element
     */
    public function next() {
        next($this->rules);
    }

    /**
     * Move the iterator pointer to the first element
     */
    public function rewind() {
        reset($this->rules);
    }

    /**
     * Validates the pointer current position
     *
     * @return bool
     */
    public function valid() {
        return current($this->rules) !== FALSE;
    }

}
