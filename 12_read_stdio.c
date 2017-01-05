/**
 * Test code to measure the time of the write() system call via the VDSO.
 * Compile and run with:
 * gcc -std=c99 write_stdio.c -o write_stdio && time ./write_stdio
 */
#include <stdio.h>
#include <unistd.h>

int main() {
	FILE* file = fopen("/dev/zero", "r");
	int data = 0;
	for(ssize_t i = 1000000; i > 0; i--) {
		fread(&data, sizeof(data), 1, file);
	}
	fclose(file);
	return 0;
}