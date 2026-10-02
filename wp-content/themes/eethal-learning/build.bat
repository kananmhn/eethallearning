@echo off
rem Builds the Talent Directory app: assets/talent-directory/src/app.jsx (and the files it imports) -> assets/talent-directory/app.js
rem Usage:  build          build once
rem         build watch    rebuild every time a file in src/ is saved (Ctrl+C to stop)
rem Works from any folder, and uses npx.cmd so PowerShell's script policy doesn't block it.

cd /d "%~dp0"

set WATCH=
if /i "%~1"=="watch" set WATCH=--watch

call npx.cmd --yes esbuild assets/talent-directory/src/app.jsx --bundle --loader:.jsx=jsx --jsx-factory=React.createElement --jsx-fragment=React.Fragment --format=iife --target=es2018 --minify --charset=utf8 --outfile=assets/talent-directory/app.js %WATCH%
