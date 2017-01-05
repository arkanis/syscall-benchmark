/**
 * Test code to measure the time of a small function call.
 * Compile and run with:
 * gcc -std=c99 null_call_stack.c -o null_call_stack && time ./null_call_stack
 */
#include <unistd.h>

int foo() {
	return 0;
}

int main() {
	for(ssize_t i = 10000000; i > 0; i--)
		foo();
	return 0;
}