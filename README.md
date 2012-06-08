PHP script to help convert C function definitions and structures to js-ctypes definitions.

Currently not very realiable; uses simple regular expressions instead of token-based processing.

Usage: `php convert.php < <in> > <out>`

Reads C code from standard input and writes results to standard output.