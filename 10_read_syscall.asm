; test code to measure time of a small system call
; compile and run with:
; yasm -f elf64 write_syscall.asm  && ld write_syscall.o -o write_syscall && time ./write_syscall

section .data
	filename: db '/dev/zero', 0

section .text
	global _start
	
	_start:
		; r14 = open("/dev/zero", O_RDONLY)
		mov rax, 2
		mov rdi, filename
		mov rsi, 0
		mov rdx, 0
		syscall
		mov r14, rax
		
		sub rsp, 4
		mov r15, 1000000
		
	read:
		; read(fd, &data, sizeof(data));
		mov rax, 0
		mov rdi, r14
		mov rsi, rsp
		mov rdx, 4
		syscall
		
		dec r15
		jnz read
		
		; close(fd)
		mov rax, 3
		mov rdi, r14
		syscall
		
		; exit(0)
		mov rax, 60
		mov rdi, 0
		syscall