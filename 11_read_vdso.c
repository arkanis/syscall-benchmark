/**
 * Test code to measure the time of the write() system call via the VDSO.
 * Compile and run with:
 * gcc -std=c99 write_vdso.c -o write_vdso && time ./write_vdso
 */
#include <sys/types.h>
#include <sys/stat.h>
#include <fcntl.h>
#include <unistd.h>

int main() {
	int fd = open("/dev/zero", O_RDONLY);
	int data = 0;
	for(ssize_t i = 1000000; i > 0; i--) {
		read(fd, &data, sizeof(data));
	}
	close(fd);
	return 0;
}