<?php

namespace php_adt;

use \StdClass as StdClass; use \Exception as Exception;import('Arr');

class CharArr extends Arr {

    /**
    * Cached string value.
    * @var string
    */
    protected $_str;

    /**
    * Constructor
    * @param string|CharArr|array $str
    */
    public function __construct($str='') {
        if (is_object($str) && $str instanceof self) {
            $str = $str->to_s();
        }
        if (!is_array($str)) {
            $chars = [];
            for ($i = 0; $i < strlen($str); $i++) {
                $chars[] = $str[$i];
            }
        }
        else {
            $chars = $str;
        }
        parent::__construct(...$chars);
        $this->cache();
    }

    // STATIC

    /**
     * Creates a new instance from an iterable.
     * @param Iterator $iterable
     * @param bool $recursive
     * @return CharArr
     */
    public static function from_iterable($iterable, $recursive=true) {
        if (is_object($iterable) && method_exists($iterable, 'to_a')) {
            $result = $iterable->to_a();
        }
        else {
            $result = [];
            foreach ($iterable as $key => $value) {
                $result[] = $value;
            }
        }
        return new static(implode('', $result));
    }

    /**
     * Creates a new instance that's filled with values according to the defined letter range.
     * @param mixed $start
     * @param mixed $end Inclusive.
     * @param number $step
     * @return Arr
     */
    public static function range($start, $end, $step=1) {
        return static::from_iterable(range($start, $end, $step));
    }

    /**
     * Stringifies the CharArr instance.
     * @return string
     */
    public function __toString() {
        return $this->to_s();
    }

    /**
    *
    */
    public function clear() {
        parent::clear();
        $this->cache();
        return $this;
    }

    public function lorem($paragraphs=1, $sep=' ') {
        $sep = $this->_to_s($sep);
        return str_repeat('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'.$sep, $paragraphs);

    }

    ////////////////////////////////////////////////////////////////////////////////////
    // PROTECTED

    protected function cache() {
        $this->_str = implode('', $this->_elements);
        return $this;
    }

    protected function _to_s($object) {
        if (is_string($object)) {
            return $object;
        }
        if (is_object($object) && $object instanceof self) {
            return $object->to_s();
        }
        throw new \Exception('Could not convert given object to native string.', 1);
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // PUBLIC

    public function copy($deep=false) {
        return static::from_iterable($this);
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING HASHABLE
    public function hash() {
        return __hash(implode('', $this->_elements));
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ABSTRACTSET

    public function get($index) {
        return $this->offsetGet($index);
    }

    // protected function _get_at($index) {
    //     return $this->offsetGet($index);
    // }

    /**
    * Indicates whether the Str instance is equals to another object.
    * @param mixed $str
    * @return bool
    */
    public function equals($str) {
        if (is_object($str) && $str instanceof self) {
            if ($this->size() !== $str->size()) {
                return false;
            }
            return $this->to_s() === $str->to_s();
        }
        elseif (is_string($str)) {
            return $this->to_s() === $str;
        }
        return false;
    }

    /**
     * Converts this Str instance to a native array.
     * @return array
     */
    public function to_a() {
        $res = [];
        foreach ($this as $char) {
            $res[] = $char;
        }
        return $res;
    }

    /**
     * Creates a copy of the Str instance.
     * @return Arr
     */
    public function to_arr() {
        return new Arr(...$this->to_a());
    }

    /**
     * Converts the Str instance to an instance of Dict (indices become the keys).
     * @return Dict
     */
    public function to_dict() {
        return new Dict(null, $this->to_a());
    }

    /**
    * Converts the Str instance to an instance of Str.
    * @return Str
    */
    public function to_str() {
        return $this->copy();
    }

    /**
    * Converts the Str instance to an instance of Set.
    * @return Set
    */
    public function to_set() {
        return new Set(...$this->to_a());
    }

    /**
    * Converts the Str instance to a string.
    * @return string
    */
    public function to_s() {
        return $this->_str;
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ARRAYACCESS

    public function offsetGet($offset) {
        $result = parent::offsetGet($offset);
        return new static($result);
    }

    /**
     * @internal
    */
    public function offsetSet($offset, $value) {
        // TODO enable slicing notation for setting substrings: $str[2:4] = new Str('ab');
        // called like $my_str[] = '2'; => concat
        if ($offset === null) {
            $this->push($value);
        }
        else {
            $this->_elements[$this->_adjust_offset($offset)] = $value;
        }
        $this->cache();
        return $this;
    }

    /**
     * @internal
    */
    public function offsetUnset($offset) {
        $offset = $this->_adjust_offset($offset);
        $str = '';
        foreach ($this as $idx => $char) {
            if ($idx !== $offset) {
                $str .= $char;
            }
        }
        $this->_str = $str;
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ITERATOR

    /**
     * Gets the current character.
     * @return mixed
     */
    public function current() {
        return new static(parent::current());
    }

    // /**
    //  * Gets the index of the current character.
    //  * @return mixed
    //  */
    // public function key() {
    //     return $this->_position;
    // }
    //
    // /**
    //  * Moves the cursor to the next key-value pair.
    //  */
    // public function next() {
    //     $this->_position++;
    // }
    //
    // /**
    //  * Moves the cursor to the first key-value pair.
    //  */
    // public function rewind() {
    //     $this->_position = 0;
    // }
    //
    // /**
    // *
    // */
    // public function valid() {
    //     return $this->_position >= 0 && $this->_position < $this->size();
    // }

    //

    public function slice($start=0, $length=null) {
        return new static(implode('', array_slice($this->_elements, $start, $length)));
    }

    // JAVA INTERFACE
    /**
    * Returns the char value at the specified index.
    * @return CharArr
    */
    public function char_at($index) {
        return $this[$index];
    }
    /**
    * Concatenates the specified strings to the end of this string.
    */
    public function concat(...$strs) {
        $chars = $this->to_s();
        foreach ($strs as $str) {
            try {
                $chars .= $this->_to_s($str);
            } catch (\Exception $e) {
                throw new \Exception('CharArr::concat: Can only concat strings and CharArr instances. Got '.$chars, 1);
            }
        }
        return new Str($chars);
    }
    /**
    * Returns true if and only if this string contains the specified sequence of char values.
    */
    public function contains($str) {
        try {
            $substr = $this->_to_s($str);
        } catch (\Exception $e) {
            throw new \Exception('CharArr::concat: Can only concat strings and CharArr instances. Got '.$str, 1);
        }
        return strpos($this->to_s(), $substr) !== false;
    }
    /**
    * Tells whether or not this string matches the given regular expression.
    * Options/flags: g global search, i case insensitive, m make dot match newlines, x ignore whitespace in regex, o perform #{...} substitutions only once
    */
    public function matches($pattern) {
        $pattern = $this->_to_s($pattern);
        $err_message = "CharArr::matches: Invalid regular expression '$pattern'.";

        $parts = explode('/', $pattern);
        $num_parts = count($parts);
        $last_part = $parts[$num_parts - 1];
        // g flag provided but this will cause an error in preg_match => remove it because it won't change the result
        if (strpos($last_part, 'g') !== false) {
            $last_part = str_replace('g', '', $last_part);
            $parts[$num_parts - 1] = $last_part;
            $pattern = implode('/', $parts);
        }

        try {
            $preg_res = preg_match($pattern, $this->to_s());
        }
        catch (\Exception $e) {
            throw new \Exception($err_message.' Error from '.$e->getmessage());
        }
        if ($preg_res === false) {
            throw new \Exception($err_message);
        }
        return $preg_res === 1;
    }
    /**
    * Returns a string that is a substring of this string.
    */
    public function substring($start=0, $end=null) {
        if ($end === null) {
            $end = $this->size();
        }
        return $this->slice($start, $end - $start);
    }
    /**
    * Returns a string whose value is this string, with any leading and trailing whitespace removed.
    */
    public function trim() {
        return new static(trim($this->to_s()));
    }

    // PYTHON INTERFACE
    /**
    * Return a copy of the string with its first character capitalized and the rest lowercased.
    */
    public function capitalize() {
        if ($this->is_empty()) {
            return new static();
        }
        return new static(strtoupper(parent::offsetGet(0)).implode('', array_slice($this->_elements, 1)));
    }
    /**
    * Return a casefolded copy of the string. Casefolded strings may be used for caseless matching.
    * Casefolding is similar to lowercasing but more aggressive because it is intended to remove all case distinctions in a string. For example, the German lowercase letter 'ß' is equivalent to "ss". Since it is already lowercase, lower() would do nothing to 'ß'; casefold() converts it to "ss".
    * The casefolding algorithm is described in section 3.13 of the Unicode Standard.
    */
    public function casefold() {
        // TODO: can this be done with a reasonable amount of effort?
    }
    /**
    * Return centered in a string of length width. Padding is done using the specified fillchar (default is an ASCII space). The original string is returned if width is less than or equal to len(s).
    */
    public function center($width, $fillchar=' ') {
        $size = $this->size();
        if ($width < $size) {
            return $this->copy();
        }
        $width -= $size;
        $first_half = ceil($width / 2);
        $second_half = floor($width / 2);
        return new static(str_repeat($fillchar, $first_half).$this->to_s().str_repeat($fillchar, $second_half));
    }
    /**
    * Return the number of non-overlapping occurrences of substring sub in the range [start, end]. Optional arguments start and end are interpreted as in slice notation.
    */
    public function count_substr($sub, $start=0, $end=null) {
        if ($end === null) {
            $end = $this->size();
        }
        return substr_count($this->to_s(), $sub, $start, $end - $start);
    }
    /**
    * Return True if the string ends with the specified suffix, otherwise return False.
    */
    public function endswith($suffix) {
        $suffix = $this->_to_s($suffix);
        if ($suffix === '') {
            return true;
        }
        $haystack = $this->to_s();
        $pos = strlen($haystack) - strlen($suffix);
        return ($pos >= 0 && strpos($haystack, $suffix, $pos) !== false);
    }
    /**
    * Return a copy of the string where all tab characters are replaced by one or more spaces, depending on the current column and the given tab size. Tab positions occur every tabsize characters (default is 4, giving tab positions at columns 0, 8, 16 and so on). To expand the string, the current column is set to zero and the string is examined character by character. If the character is a tab (\t), one or more space characters are inserted in the result until the current column is equal to the next tab position. (The tab character itself is not copied.) If the character is a newline (\n) or return (\r), it is copied and the current column is reset to zero. Any other character is copied unchanged and the current column is incremented by one regardless of how the character is represented when printed.
    */
    public function expandtabs($tabsize=4) {
        return new static(str_replace("\t", str_repeat(' ', $tabsize), $this->to_s()));
    }
    /**
    * Return the lowest index in the string where substring sub is found within the slice s[start:end]. Optional arguments start and end are interpreted as in slice notation. Return -1 if sub is not found.
    * @return int
    */
    public function find($sub, $start=0, $end=null) {
        $sub = $this->_to_s($sub);
        $res = strpos($this->to_s(), $sub, $start);
        if ($res === false) {
            return -1;
        }
        if ($end === null) {
            $end = $this->size();
        }
        if ($res <= $end - strlen($sub)) {
            return $res;
        }
        return -1;
    }
    /**
    * Perform a string formatting operation. The string on which this method is called can contain literal text or replacement fields delimited by braces {}.
    * Each replacement field contains either the numeric index of a positional argument, or the name of a keyword argument. Returns a copy of the string where each replacement field is replaced with the string value of the corresponding argument.
    * This method will internally use multiple calls of str_replace. For that reason using e.g. '{0}'.format('{1}', 2) will result in '2';
    * @param string... $args Each (except the last) argument must be a string or an object with a <code>__toString()</code> method. The last argument may be an associative array or a Dict instance. If it's a Dict the keys must be strings of have a <code>__toString()</code> method.
    * @throws Exception
    */
    public function format(...$args) {
        // TODO: does it make sense to implement all of python's cababilities (https://pyformat.info/) ? probably not...
        $last_arg = $args[count($args) - 1];
        unset($args[count($args) - 1]);
        // merge associative last argument into args because those key-value pairs are handled normally (because the keys are strings)
        if (is_array($last_arg)) {
            // manual merge because array_merge will change indices
            foreach ($last_arg as $key => $value) {
                $args[$key] = $value;
            }
            $kwargs = null;
        }
        elseif (is_object($last_arg) && $last_arg instanceof Dict) {
            $kwargs = $last_arg;
        }

        $new_res = $this->to_s();
        $new_res_len = strlen($new_res);
        $res = '';
        $replace_count = 0;
        // fill empty '{}'s with indices
        for ($i = 0; $i < $new_res_len - 1; $i++) {
            // append char
            if (!($new_res[$i] === '{' && $new_res[$i + 1] === '}')) {
                $res .= $new_res[$i];
            }
            else {
                $res .= '{'.$replace_count.'}';
                $replace_count++;
                $i++;
            }
        }
        // append last char because we skipped it in the loop for easier checking
        $res .= $new_res[$new_res_len -1];
        unset($new_res);
        $num_args = count($args);

        foreach ($args as $key => $value) {
            $res = str_replace('{'.$key.'}', $value.'', $res);
        }
        if ($kwargs !== null) {
            foreach ($kwargs as $key => $value) {
                $res = str_replace('{'.$key.'}', $value.'', $res);
            }
        }
        return $res;
    }
    /**
    * Like find(), but raise ValueError when the substring is not found.
    * @throws Exception
    */
    public function index($sub, $start=0, $end=null, $equality=null) {
        $res = $this->find($sub, $start, $end);
        if ($res !== -1) {
            return $res;
        }
        throw new \Exception("CharArr::index: Could not find '$sub' in '".$this->to_s()."'.", 1);
    }

    // /**
    // * Return true if all characters in the string are alphanumeric and there is at least one character, false otherwise. A character c is alphanumeric if one of the following returns True: c.isalpha(), c.isdecimal(), c.isdigit(), or c.isnumeric().
    // */
    // public function isalnum() {
    //
    // }
    // /**
    // * Return true if all characters in the string are alphabetic and there is at least one character, false otherwise. Alphabetic characters are those characters defined in the Unicode character database as “Letter”, i.e., those with general category property being one of “Lm”, “Lt”, “Lu”, “Ll”, or “Lo”. Note that this is different from the “Alphabetic” property defined in the Unicode Standard.
    // */
    // public function isalpha() {
    //
    // }
    // /**
    // * Return true if all characters in the string are decimal characters and there is at least one character, false otherwise. Decimal characters are those from general category “Nd”. This category includes digit characters, and all characters that can be used to form decimal-radix numbers, e.g. U+0660, ARABIC-INDIC DIGIT ZERO.
    // */
    // public function isdecimal() {
    //
    // }
    // /**
    // * Return true if all characters in the string are digits and there is at least one character, false otherwise. Digits include decimal characters and digits that need special handling, such as the compatibility superscript digits. Formally, a digit is a character that has the property value Numeric_Type=Digit or Numeric_Type=Decimal.
    // */
    // public function isdigit() {
    //
    // }
    // /**
    // * Return true if all cased characters in the string are lowercase and there is at least one cased character, false otherwise.
    // */
    // public function islower() {
    //
    // }
    // /**
    // * Return true if all characters in the string are numeric characters, and there is at least one character, false otherwise. Numeric characters include digit characters, and all characters that have the Unicode numeric value property, e.g. U+2155, VULGAR FRACTION ONE FIFTH. Formally, numeric characters are those with the property value Numeric_Type=Digit, Numeric_Type=Decimal or Numeric_Type=Numeric.
    // */
    // public function isnumeric() {
    //
    // }
    // /**
    // * Return true if all characters in the string are printable or the string is empty, false otherwise. Nonprintable characters are those characters defined in the Unicode character database as “Other” or “Separator”, excepting the ASCII space (0x20) which is considered printable. (Note that printable characters in this context are those which should not be escaped when repr() is invoked on a string. It has no bearing on the handling of strings written to sys.stdout or sys.stderr.)
    // */
    // public function isprintable() {
    //
    // }
    // /**
    // * Return true if there are only whitespace characters in the string and there is at least one character, false otherwise. Whitespace characters are those characters defined in the Unicode character database as “Other” or “Separator” and those with bidirectional property being one of “WS”, “B”, or “S”.
    // */
    // public function isspace() {
    //
    // }

    /**
    * Return true if the string is a titlecased string and there is at least one character, for example uppercase characters may only follow uncased characters and lowercase characters only cased ones. Return false otherwise.
    */
    public function istitle() {
        return !$this->is_empty() && $this->equals($this->title());
    }

    /**
    * Return true if all cased characters in the string are uppercase and there is at least one cased character, false otherwise.
    */
    public function isupper() {
        return !$this->is_empty() && $this->to_s() === strtoupper($this->to_s());
    }

    /**
    * Return a string which is the concatenation of the strings in the iterable iterable. A TypeError will be raised if there are any non-string values in iterable, including bytes objects. The separator between elements is the string providing this method.
    */
    public function join($iterable=[]) {
        $parts = [];
        foreach ($iterable as $key => $value) {
            $parts[] = $value;
        }
        return new static(implode($this->to_s(), $parts));
    }

    /**
    * Return a copy of the string with all the cased characters converted to lowercase.
    * The lowercasing algorithm used is described in section 3.13 of the Unicode Standard.
    */
    public function lower() {
        return new static(strtolower($this->to_s()));
    }

    /**
    * Split the string at the first occurrence of sep, and return a 3-tuple containing the part before the separator, the separator itself, and the part after the separator. If the separator is not found, return a 3-tuple containing the string itself, followed by two empty strings.
    */
    public function partition($sep) {
        $sep = $this->_to_s($sep);
        $str = $this->to_s();
        $pos = strpos($str, $sep);
        if ($pos !== false) {
            return new Arr(new static(substr($str, 0, $pos)), new static($sep), new static(substr($str, $pos + strlen($sep))));
        }
        return new Arr($this->copy(), new static(), new static());
    }

    /**
    * Return a copy of the string with all occurrences of substring old replaced by new. If the optional argument count is given, only the first count occurrences are replaced.
    */
    public function replace($old, $new=null, $count=null) {
        $old = $this->_to_s($old);
        $str = $this->to_s();
        $pos = strpos($str, $old);
        if ($pos === false) {
            return $this->copy();
        }

        $new = $this->_to_s($new);
        if ($count === null) {
            return new static(str_replace($old, $new, $str));
        }
        // find the last of $cound occurrences of $old
        $i = 1; // because the first $pos was already retrived above
        $n = strlen($old);
        while($i < $count) {
            $tmp = strpos($str, $old, $pos + $n);
            if ($tmp === false) {
                // last pos before tmp is saved
                break;
            }
            $pos = $tmp;
            $i++;
        }
        return new static(str_replace($old, $new, substr($str, 0, $pos + $n)).substr($str, $pos + $n));
    }

    /**
    * Return a list of the words in the string, using sep as the delimiter string. If maxsplit is given, at most maxsplit splits are done (thus, the list will have at most maxsplit+1 elements). If maxsplit is not specified or -1, then there is no limit on the number of splits (all possible splits are made).
    * If sep is not specified or is None, any whitespace string is a separator and empty strings are removed from the result.
    */
    public function split($sep='/\s+/', $maxsplit=-1) {
        $sep = $this->_to_s($sep);
        if (strlen($sep) < 2 || ($sep[0] !== '/' && $sep[strlen($sep) - 1] !== '/')) {
            $sep = '/'.$sep.'/';
        }
        return new Arr(...preg_split($sep, $this->to_s(), $maxsplit));
    }

    /**
    * Return a list of the lines in the string, breaking at line boundaries. Line breaks are not included in the resulting list unless keepends is given and true.
    */
    public function splitlines($keepends=false) {
        $pattern = '/(\n|\r\n|\r)/';
        // PREG_SPLIT_DELIM_CAPTURE
        if (!$keepends) {
            return $this->split($pattern);
        }
        // $splitted = preg_split($sep, $this->to_s());
        $splitted = preg_split($pattern, $this->to_s(), -1, PREG_SPLIT_DELIM_CAPTURE);
        $parts = [];
        for ($i = 0; $i < count($splitted); $i += 2) {
            if (isset($splitted[$i + 1])) {
                $parts[] = $splitted[$i].$splitted[$i + 1];
            }
            else {
                $parts[] = $splitted[$i];
            }
        }
        return new Arr(...$parts);
    }

    /**
    * Return True if string starts with the prefix, otherwise return False. prefix can also be a tuple of prefixes to look for.
    */
    public function startswith($prefix) {
        // search backwards starting from haystack length characters from the end
        $str = $this->to_s();
        return $prefix === "" || strrpos($str, $prefix, -strlen($str)) !== false;
    }

    /**
    * Return a copy of the string with the leading and trailing characters removed. The chars argument is a string specifying the set of characters to be removed. If omitted or null, the chars argument defaults to removing whitespace. The chars argument is not a prefix or suffix; rather, all combinations of its values are stripped.
    */
    public function strip($chars=null) {
        $str = $this->to_s();
        if ($chars === null) {
            return new static(trim($str));
        }
        $chars = $this->_to_s($chars);
        return preg_replace('/(^['.$chars.']*|['.$chars.']*$)/', '', $str);
    }
    /**
    * Return a copy of the string with uppercase characters converted to lowercase and vice versa. Note that it is not necessarily true that s.swapcase().swapcase() == s.
    */
    public function swapcase($target_case=null) {

    }
    /**
    * Return a titlecased version of the string where words start with an uppercase character and the remaining characters are lowercase.
    */
    public function title() {

    }

    /**
    * Return a copy of the string with all the cased characters [4] converted to uppercase. Note that str.upper().isupper() might be False if s contains uncased characters or if the Unicode category of the resulting character(s) is not “Lu” (Letter, uppercase), but e.g. “Lt” (Letter, titlecase).
    */
    public function upper() {

    }
    /**
    * Return a copy of the string left filled with ASCII '0' digits to make a string of length width. A leading sign prefix ('+'/'-') is handled by inserting the padding after the sign character rather than before. The original string is returned if width is less than or equal to len(s).
    */
    public function zfill($width) {

    }
}
// namespace dependent class aliasing
$ns_prefix = __NAMESPACE__ == '' ? '' : __NAMESPACE__.'\\';
class_alias($ns_prefix.'CharArr', $ns_prefix.'Str');
