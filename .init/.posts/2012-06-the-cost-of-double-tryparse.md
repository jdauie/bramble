After extensive testing, I have optimized the text to binary conversion for ASCII XYZ files.  These files are a simple delimited format with XYZ values along with any number of additional attributes. My original naive approach used [`StreamReader.ReadLine()`][readline] and [`Double.TryParse()`][tryparse].  I quickly discovered that when converting billions of points from XYZ to binary, the parse time was far slower than the output time, making it the key bottleneck for the process.

Although the TryParse methods are convenient for normal use, they are far too slow for my purposes, since the points are in a simple floating point representation.  I implemented a reader to optimize the parse for that case, ignoring culture rules, scientific notation, and many other cases that are normally handled within the TryParse.  In addition, the parse performance of [`atof()`][atof]-style operations varies considerably between implementations.  The best performance I could come up with was a simple variation of a common idea with the addition of a lookup table.  The main cost of the parse is still the conditional branches.

In the end, I used custom parsing to identify lines directly in bytes without the overhead of memory allocation/copying and converting to unicode strings.  From there, I parse out the digits using the method I described.  I also made a variation that used only incremented pointers instead of array indices, but in the current .NET version, the performance was practically identical, so I reverted to the index version for ease of debugging.

The following test code provides reasonable performance for parsing three double-precision values per line.

~~~ {csharp}
bool ParseXYZ(byte* p, int start, int end, double* xyz)
{
	for (int i = 0; i < 3; i++)
	{
		long digits = 0;

		// find start
		while (start < end && (p[start] < '0' || p[start] > '9'))
			++start;

		// accumulate digits (before decimal separator)
		int currentstart = start;
		while (start < end && (p[start] >= '0' && p[start] <= '9'))
		{
			digits = 10 * digits + (p[start] - '0');
			++start;
		}

		// check for decimal separator
		if (start > currentstart && start < end && p[start] == '.')
		{
			int decimalPos = start;
			++start;

			// accumulate digits (after decimal separator)
			while (start < end && (p[start] >= '0' && p[start] <= '9'))
			{
				digits = 10 * digits + (p[start] - '0');
				++start;
			}

			xyz[i] = digits * c_reciprocal[start - decimalPos - 1];
		}
		else
			xyz[i] = digits;

		if (start == currentstart || digits < 0)
			return false; // no digits or too many (overflow)
	}

	return true;
}
~~~

[readline]: http://msdn.microsoft.com/en-us/library/system.io.streamreader.readline.aspx "StreamReader.ReadLine Method"
[tryparse]: http://msdn.microsoft.com/en-us/library/system.double.tryparse.aspx "Double.TryParse Method"
[atof]: http://www.cplusplus.com/reference/clibrary/cstdlib/atof/ "atof"
