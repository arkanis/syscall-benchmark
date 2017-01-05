/**
 * Test code to measure the time of a small system call via the VDSO.
 * Compile and run with:
 * gcc -std=c99 getpid_vdso.c -o getpid_vdso && time ./getpid_vdso
 */
#include <sys/types.h>
#include <unistd.h>

int main() {
	for(ssize_t i = 10000000; i > 0; i--)
		getpid();
	
	return 0;
}