<?php

require_once '_php_adt/AbstractSet.php';
// require_once '_php_adt/Clonable.php';
// require_once '_php_adt/Hashable.php';
// import('Clonable', '_php_adt');
// import('Hashable', '_php_adt');

// use _php_adt\Clonable as Clonable;
// use _php_adt\Hashable as Hashable;
use _php_adt\AbstractSet as AbstractSet;

class Str extends AbstractSet {
    /**
    * @var string
    */
    protected $_str;
    /**
    * @internal
    * @var int
    */
    protected $_position;

    public function __construct($str='') {
        $this->_str = $str;
        $this->_position = 0;
    }


    ////////////////////////////////////////////////////////////////////////////////////
    // PROTECTED

    ////////////////////////////////////////////////////////////////////////////////////
    // PUBLIC

    public function copy($deep=false) {
        return __clone($this->_str);
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING HASHABLE
    public function hash() {
        return __hash($this->_str);
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ABSTRACTSET

    /**
    * Empties the Str instance. <span class="label label-info">Chainable</span>
    * @return Str
    */
    public function clear() {
        $this->_str = '';
        $this->_position = 0;
        return $this;
    }

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
            // hashes are equal => compare each entry
            foreach ($this as $idx => $char) {
                if ($char !== $str[$idx]) {
                    return false;
                }
            }
            return true;
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
            $res[] = $element;
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
        return new Arr(null, $this->to_a());
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

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ARRAYACCESS
    // TODO: somehow share code with Arr class (sequence should NOT inherit from collection though)
    /**
     * @internal
    */
    protected function _adjust_offset($offset) {
        if ($this->offsetExists($offset)) {
            if ($offset < 0) {
                $offset += $this->size();
            }
            return $offset;
        }
        throw new Exception("Undefined offset $offset!", 1);
    }

    /**
     * @internal
    */
    protected function _get_start_end_from_offset($offset) {
        if (is_array($offset)) {
            if (is_int($offset[0]) && is_int($offset[1])) {
                $use_slicing = true;
                $start = $offset[0];
                $end = $offset[1];
            }
            else {
                throw new Exception('Invalid array offset '.__toString($offset).'. Array offsets must have the form \'[int1,int2]\'.');
            }
        }
        else if (is_string($offset)) {
            $offset = preg_replace('/\s+/', '', $offset);
            if (preg_match('/^\-?\d+\:(\-?\d+)?$/', $offset) === 1) {
                $use_slicing = true;
                $parts = explode(':', $offset);
                $start = (int) $parts[0];
                if (strlen($parts[1]) > 0) {
                    $end = (int) $parts[1];
                }
                else {
                    $end = null;
                }
            }
            else {
                throw new Exception('Invalid string offset \''.$offset.'\'. String offsets must have the form \'int1:(int2)\'.');
            }
        }
        else if (is_int($offset)) {
            $use_slicing = false;
            $start = $offset;
            $end = $offset;
        }
        else {
            throw new Exception('Invalid offset. Use null ($a[]=4) to push, int for index access or for slicing use [start, end] or \'start:end\'!');
        }
        try {
            $end = $this->_adjust_offset($end);
        } catch (Exception $e) {
            $end = $this->size();
        }

        return [
            'start' => $this->_adjust_offset($start),
            'end' => $end,
            'slicing' => $use_slicing
        ];
    }

    /**
     * @internal
    */
    public function offsetExists($offset) {
        if (is_int($offset)) {
            if ($offset >= 0) {
                return $offset < $this->size();
            }
            // else: negative
            return abs($offset) <= $this->size();
        }
        return false;
    }

    /**
     * @internal
    */
    public function offsetGet($offset) {
        $bounds = $this->_get_start_end_from_offset($offset);
        if (!$bounds['slicing']) {
            return $this->_elements[$bounds['start']];
        }
        return $this->slice($bounds['start'], $bounds['end'] - $bounds['start']);
    }

    /**
     * @internal
    */
    public function offsetSet($offset, $value) {
        // TODO enable slicing notation for setting subarrays: $arr[2:4] = new Arr(12,13);
        // called like $my_arr[] = 2; => push
        if ($offset === null) {
            $this->push($value);
        }
        else {
            $this->_elements[$this->_adjust_offset($offset)] = $value;
        }
        return $this;
    }

    /**
     * @internal
    */
    public function offsetUnset($offset) {
        if ($this->offsetExists($offset)) {
            unset($this->_elements[$offset]);
            // reassign keys
            $this->_elements = array_values($this->_elements);
        }
    }

    ////////////////////////////////////////////////////////////////////////////////////
    // IMPLEMENTING ITERATOR

    /**
     * Gets the current character.
     * @return mixed
     */
    public function current() {
        return $this->_str[$this->_position];
    }

    /**
     * Gets the index of the current character.
     * @return mixed
     */
    public function key() {
        return $this->_position;
    }

    /**
     * Moves the cursor to the next key-value pair.
     */
    public function next() {
        $this->_position++;
    }

    /**
     * Moves the cursor to the first key-value pair.
     */
    public function rewind() {
        $this->_position = 0;
    }

    /**
    *
    */
    public function valid() {
        return $this->_position >= 0 && $this->_position < $this->size();
    }

    //

    public function is_empty() {
        return $this->size() === 0;
    }
    public function size() {

    }

    // JAVA INTERFACE
    /**
    * Returns the char value at the specified index.
    */
    public function char_at($index) {
        return $this[$index];
    }
    /**
    * Concatenates the specified string to the end of this string.
    */
    public function concat($str) {
        return new Str($this->_str.$str->to_str());
    }
    /**
    * Returns true if and only if this string contains the specified sequence of char values.
    */
    public function contains($str) {

    }
    /**
    * Tells whether or not this string matches the given regular expression.
    */
    public function matches($pattern) {

    }
    /**
    * Returns a string that is a substring of this string.
    */
    public function substring() {

    }
    /**
    * Returns a string whose value is this string, with any leading and trailing whitespace removed.
    */
    public function trim() {

    }

    // PYTHON INTERFACE
    /**
    * Return a copy of the string with its first character capitalized and the rest lowercased.
    */
    public function capitalized() {

    }
    /**
    * Return a casefolded copy of the string. Casefolded strings may be used for caseless matching.
    * Casefolding is similar to lowercasing but more aggressive because it is intended to remove all case distinctions in a string. For example, the German lowercase letter 'ß' is equivalent to "ss". Since it is already lowercase, lower() would do nothing to 'ß'; casefold() converts it to "ss".
    * The casefolding algorithm is described in section 3.13 of the Unicode Standard.
    */
    public function casefold() {

    }
    /**
    * Return centered in a string of length width. Padding is done using the specified fillchar (default is an ASCII space). The original string is returned if width is less than or equal to len(s).
    */
    public function center($width, $fillchar=' ') {

    }
    /**
    * Return the number of non-overlapping occurrences of substring sub in the range [start, end]. Optional arguments start and end are interpreted as in slice notation.
    */
    public function count_py($sub, $start=0, $end=null) {

    }
    /**
    * Return True if the string ends with the specified suffix, otherwise return False. suffix can also be a tuple of suffixes to look for. With optional start, test beginning at that position. With optional end, stop comparing at that position.
    */
    public function endswith($suffix, $start=0, $end=null) {

    }
    /**
    * Return a copy of the string where all tab characters are replaced by one or more spaces, depending on the current column and the given tab size. Tab positions occur every tabsize characters (default is 4, giving tab positions at columns 0, 8, 16 and so on). To expand the string, the current column is set to zero and the string is examined character by character. If the character is a tab (\t), one or more space characters are inserted in the result until the current column is equal to the next tab position. (The tab character itself is not copied.) If the character is a newline (\n) or return (\r), it is copied and the current column is reset to zero. Any other character is copied unchanged and the current column is incremented by one regardless of how the character is represented when printed.
    */
    public function expandtabs($tabsize=4) {

    }
    /**
    * Return the lowest index in the string where substring sub is found within the slice s[start:end]. Optional arguments start and end are interpreted as in slice notation. Return -1 if sub is not found.
    */
    public function find($sub, $start=0, $end=null) {

    }
    /**
    * Perform a string formatting operation. The string on which this method is called can contain literal text or replacement fields delimited by braces {}. Each replacement field contains either the numeric index of a positional argument, or the name of a keyword argument. Returns a copy of the string where each replacement field is replaced with the string value of the corresponding argument.
    */
    public function format($args, $kwargs) {

    }
    /**
    * Like find(), but raise ValueError when the substring is not found.
    */
    public function index($sub, $start=0, $end=null) {

    }
    /**
    * Return true if all characters in the string are alphanumeric and there is at least one character, false otherwise. A character c is alphanumeric if one of the following returns True: c.isalpha(), c.isdecimal(), c.isdigit(), or c.isnumeric().
    */
    public function isalnum() {

    }
    /**
    * Return true if all characters in the string are alphabetic and there is at least one character, false otherwise. Alphabetic characters are those characters defined in the Unicode character database as “Letter”, i.e., those with general category property being one of “Lm”, “Lt”, “Lu”, “Ll”, or “Lo”. Note that this is different from the “Alphabetic” property defined in the Unicode Standard.
    */
    public function isalpha() {

    }
    /**
    * Return true if all characters in the string are decimal characters and there is at least one character, false otherwise. Decimal characters are those from general category “Nd”. This category includes digit characters, and all characters that can be used to form decimal-radix numbers, e.g. U+0660, ARABIC-INDIC DIGIT ZERO.
    */
    public function isdecimal() {

    }
    /**
    * Return true if all characters in the string are digits and there is at least one character, false otherwise. Digits include decimal characters and digits that need special handling, such as the compatibility superscript digits. Formally, a digit is a character that has the property value Numeric_Type=Digit or Numeric_Type=Decimal.
    */
    public function isdigit() {

    }
    /**
    * Return true if all cased characters in the string are lowercase and there is at least one cased character, false otherwise.
    */
    public function islower() {

    }
    /**
    * Return true if all characters in the string are numeric characters, and there is at least one character, false otherwise. Numeric characters include digit characters, and all characters that have the Unicode numeric value property, e.g. U+2155, VULGAR FRACTION ONE FIFTH. Formally, numeric characters are those with the property value Numeric_Type=Digit, Numeric_Type=Decimal or Numeric_Type=Numeric.
    */
    public function isnumeric() {

    }
    /**
    * Return true if all characters in the string are printable or the string is empty, false otherwise. Nonprintable characters are those characters defined in the Unicode character database as “Other” or “Separator”, excepting the ASCII space (0x20) which is considered printable. (Note that printable characters in this context are those which should not be escaped when repr() is invoked on a string. It has no bearing on the handling of strings written to sys.stdout or sys.stderr.)
    */
    public function isprintable() {

    }
    /**
    * Return true if there are only whitespace characters in the string and there is at least one character, false otherwise. Whitespace characters are those characters defined in the Unicode character database as “Other” or “Separator” and those with bidirectional property being one of “WS”, “B”, or “S”.
    */
    public function isspace() {

    }
    /**
    * Return true if the string is a titlecased string and there is at least one character, for example uppercase characters may only follow uncased characters and lowercase characters only cased ones. Return false otherwise.
    */
    public function istitle() {

    }
    /**
    * Return true if all cased characters [4] in the string are uppercase and there is at least one cased character, false otherwise.
    */
    public function isupper() {

    }
    /**
    * Return a string which is the concatenation of the strings in the iterable iterable. A TypeError will be raised if there are any non-string values in iterable, including bytes objects. The separator between elements is the string providing this method.
    */
    public function join($iterable) {
        // use __toString()
    }

    /**
    * Return a copy of the string with all the cased characters converted to lowercase.
    * The lowercasing algorithm used is described in section 3.13 of the Unicode Standard.
    */
    public function lower() {

    }
    /**
    * Split the string at the first occurrence of sep, and return a 3-tuple containing the part before the separator, the separator itself, and the part after the separator. If the separator is not found, return a 3-tuple containing the string itself, followed by two empty strings.
    */
    public function partition($sep) {

    }
    /**
    * Return a copy of the string with all occurrences of substring old replaced by new. If the optional argument count is given, only the first count occurrences are replaced.
    */
    public function replace($old, $new, $count=null) {

    }
    /**
    * Return a list of the words in the string, using sep as the delimiter string. If maxsplit is given, at most maxsplit splits are done (thus, the list will have at most maxsplit+1 elements). If maxsplit is not specified or -1, then there is no limit on the number of splits (all possible splits are made).
    */
    public function split($sep=null, $maxsplit=-1) {

    }
    /**
    * Return a list of the lines in the string, breaking at line boundaries. Line breaks are not included in the resulting list unless keepends is given and true.
    */
    public function splitlines($keepends=false) {

    }

    /**
    * Return True if string starts with the prefix, otherwise return False. prefix can also be a tuple of prefixes to look for. With optional start, test string beginning at that position. With optional end, stop comparing string at that position.
    */
    public function startswith($prefix, $start=0, $end=null) {

    }

    /**
    * Return a copy of the string with the leading and trailing characters removed. The chars argument is a string specifying the set of characters to be removed. If omitted or null, the chars argument defaults to removing whitespace. The chars argument is not a prefix or suffix; rather, all combinations of its values are stripped.
    */
    public function strip($chars=null) {

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
