---
id: javascript-styleguide
title: JavaScript Style Guide
layout: docs
---

<h1>JavaScript Style Guide</h1>
<p>This will have, may be, has been the official JavaScript style guide for developers of Symphony CMS.</p>


<h2>Tabs vs. Spaces</h2>
<p>Spaces are evil. Always use 4–space wide tabs while developing for Symphony CMS. <em>Yes, I realise that the examples on this page use 4–space indentation and not tabs. Do what I say!</em></p>


<h2>Semicolons</h2>
<p>Apparently, some people don’t like using semicolons to terminate statements. Failing to do so may get you flayed.</p>

<h4>Right</h4>
<pre>var value = 'example';</pre>

<h4>Wrong</h4>
<pre>var value = 'example'</pre>


<h2>Trailing whitespace</h2>
<p>Just like you brush your teeth after every meal, clean up trailing whitespace in your JavaScript files before committing. Otherwise the rotten smell of careless neglect will eventually drive away contributors and/or co–workers.</p>


<h2>Line length</h2>
<p>Most reasonable text editors (Textmate and Sublime Text to name a couple) allow intelligent word-wrapping inside of your window. We suggest you use this instead of manually wrapping your lines to a fixed number of characters.</p>
<p>If you really must manually wrap some text, then do it to the standard 80 characters.</p>


<h2>Quotes</h2>
<p>Use single quotes, unless you are writing JSON.</p>

<h4>Right</h4>
<pre>var value = 'example';</pre>

<h4>Wrong</h4>
<pre>var value = "example";</pre>


<h2>Braces</h2>
<p>Your opening braces go on the same line as the statement. Also, don’t be afraid to use whitespace.</p>

<h4>Right</h4>
<pre>if (true) {
    console.log('great');
}</pre>

<h4>Wrong</h4>
<pre>if (false)
{
    console.log('never');
}</pre>


<h2>Variable declarations</h2>
<p>Don’t overuse <code>var</code> statements, always indent each variable on its own line.</p>

<h4>Right</h4>
<pre>var foo = 1,
    bar = 'two';</pre>

<h4>Wrong</h4>
<pre>var foo = 1;
var bar = 'two';</pre>

<h4>Also wrong</h4>
<pre>var foo = 1, var bar = 'two';</pre>


<h2>Variable and property names</h2>
<p>Variables and properties should use <a href="http://en.wikipedia.org/wiki/camelCase#Variations_and_synonyms">lower camel case</a> capitalization. They should also be descriptive. Single-character variables and uncommon abbreviations should generally be avoided <em>(may get you shot)</em>.</p>

<h4>Right</h4>
<pre>var translatedString = Symphony.Language.Dictionary[string];</pre>

<h4>Wrong</h4>
<pre>var translated_string = Symphony.Language.Dictionary[string];</pre>

<h4>Murder</h4>
<pre>var ts = Symphony.Language.Dictionary[string];</pre>


<h2>Class names</h2>
<p>Class names should be capitalized using <a href="http://en.wikipedia.org/wiki/camelCase#Variations_and_synonyms">upper camel case</a>.</p>

<h4>Right</h4>
<pre>function YourClass() { ... }</pre>

<h4>Wrong</h4>
<pre>function your_class() { ... }</pre>


<h2>Constants</h2>
<p>Constants should be declared as regular variables or static class properties, using all uppercase letters.</p>

<h4>Right</h4>
<pre>var SECOND = 1 * 1000;</pre>

<h4>Wrong</h4>
<pre>var second = 1 * 1000;</pre>

<h2>Object and Array creation</h2>
<p>Use trailing commas and put short declarations on a single line. Only quote keys when your interpreter complains:</p>

<h4>Right</h4>
<pre>var a = ['hello', 'world'];
var b = {
    good: 'code',
    'is generally': 'pretty',
};</pre>

<h4>Wrong</h4>
<pre>var a = [
  'hello', 'world'
];
var b = {"good": 'code'
        , is generally: 'pretty'
        };</pre>


<h2>Conditions</h2>
<p>Any non–trivial conditions should be assigned to a descriptive variable:</p>

<h4>Right</h4>
<pre>var isAuthorized = (user.isAdmin() || user.isModerator());

if (isAuthorized) {
    console.log('winning');
}</pre>

<h4>Wrong</h4>
<pre>if (user.isAdmin() || user.isModerator()) {
    console.log('losing');
}</pre>


<h2>Function length</h2>
<p>Keep your functions short. A good function fits on a slide that the people in the last row of a big room can comfortably read. So don’t count on them having perfect vision and limit yourself to ~10 lines of code per function.</p>


<h2>Return statements</h2>
<p>To avoid deep nesting of if–statements, always return a functions value as early as possible.</p>

<h4>Right</h4>
<pre>function isPercentage(val) {
    if (val &lt; 0) {
        return false;
    }

    if (val &gt; 100) {
        return false;
    }

    return true;
}</pre>

<h4>Wrong</h4>
<pre>function isPercentage(val) {
    if (val &gt;= 0) {
        if (val &lt; 100) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }
}</pre>

<p>Or for this particular example it may also be fine to shorten things even further:</p>

<pre>function isPercentage(val) {
    var isInRange = (val &gt;= 0 &amp;&amp; val &lt;= 100);

    return isInRange;
}</pre>


<h2>Else statements</h2>
<p>Your else and else if statements should find themselves on a new line, and not on the same line as a brace.</p>

<h4>Right</h4>
<pre>// We don't care:
if (val &lt; 100) {
    return false;
}

// Value is in range:
else {
    return true;
}</pre>

<h4>Wrong</h4>
<pre>// We don't care:
if (val &lt; 100) {
    return false;
} else {
    // Value is in range:
    return true;
}</pre>


<h2>Named closures</h2>
<p>Feel free to give your closures a name. It shows that you care about them, and will produce better stack traces:</p>

<h4>Right</h4>
<pre>req.on('end', function onEnd() {
    console.log('winning');
});</pre>

<h4>Wrong</h4>
<pre>req.on('end', function() {
    console.log('failing');
});</pre>
