; test code to measure time of a small function call
; compile and run with:
; yasm -f elf64 null_call_regs.asm  && ld null_call_regs.o -o null_call_regs && time ./null_call_regs

section .text
	global _start
	
	_start:
		mov r15, 10000000
		
	call_func:
		call func
		
		dec r15
		jnz call_func
		
		mov rax, 60
		mov rdi, 0
		syscall
	
	func:
		push   rbp
		mov    rbp, rsp
		mov    rax, 0
		pop    rbp
		ret