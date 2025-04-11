#!/usr/bin/env node
/* eslint-disable no-console */

/**
 * Distribution script for Object Cache Annihilator plugin.
 *
 * Usage:
 *   node bin/distribute.js
 */

import { execSync } from 'child_process';
import { fileURLToPath } from 'url';
import { dirname, join } from 'path';
import { mkdirSync, existsSync } from 'fs';

const mode = process.argv[ 2 ] === '--mode' ? process.argv[ 3 ] : 'production';
const __filename = fileURLToPath( import.meta.url );
const __dirname = dirname( __filename );
const rootDir = join( __dirname, '..' );
const distDir = join( rootDir, 'dist' );

if ( ! existsSync( distDir ) ) {
	mkdirSync( distDir, { recursive: true } );
}

console.log( '🧹 Cleaning up...' );
if ( mode === 'production' ) {
	execSync( 'rm -rf vendor', { cwd: rootDir } );
	execSync( 'rm -f composer.lock', { cwd: rootDir } );

	console.log( '📦 Installing dependencies...' );
	execSync( 'composer install --optimize-autoloader --no-dev', {
		cwd: rootDir,
	} );
}
execSync( 'rm -rf dist/*', { cwd: rootDir } );

console.log( '📦 Creating distribution...' );
let version;
try {
	version = execSync( 'git rev-parse --short HEAD', {
		encoding: 'utf8',
	} ).trim();
} catch ( error ) {
	console.log( '⚠️  Could not get git revision, using timestamp as version' );
	version = new Date().toISOString().slice( 0, 10 ).replace( /-/g, '' );
}

const distName = `object-cache-annihilator-${ version }${
	mode === 'development' ? '-dev' : ''
}`;

console.log( '🗜️ Creating ZIP file...' );
// Create a temporary directory for the distribution
const tempDir = join( rootDir, 'temp' );
execSync( `mkdir -p ${ tempDir }`, { cwd: rootDir } );

// Copy files to temp directory excluding ignored files
execSync( `rsync -av --exclude-from=.distignore . ${ tempDir }/`, {
	cwd: rootDir,
} );

// Create ZIP from temp directory
execSync( `cd ${ tempDir } && zip -r ../dist/${ distName }.zip .`, {
	cwd: rootDir,
} );

// Clean up temp directory
execSync( `rm -rf ${ tempDir }`, { cwd: rootDir } );

console.log(
	`✨ Distribution created at: ${ join( distDir, `${ distName }.zip` ) }`
);
