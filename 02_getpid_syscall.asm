; test code to measure time of a small system call
; compile and run with:
; yasm -f elf64 getpid-syscall.asm  && ld getpid-syscall.o -o getpid-syscall && time ./getpid-syscall

section .text
	global _start
	
	_start:
		mov r15, 10000000
		
	getpid:
		mov rax, 39
		syscall
		
		dec r15
		jnz getpid
		
		mov rax, 60
		mov rdi, 0
		syscall