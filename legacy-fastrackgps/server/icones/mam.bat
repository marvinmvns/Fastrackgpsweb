@echo off
setlocal EnableDelayedExpansion
set i=0
for %%a in (*.png) do (
    set /a i+=1
    ren %%a !i!.new
)
ren *.new *.png