# Flyingcodfish's TTS Deck Factory

This project consists of two main parts:
- A set of tools to convert decklists from popular TCGs into JSON files readable by Tabletop Simulator
- A website that provides these tools in an accessible, user-friendly way

## Deck Conversion Tools

Currently the deck conversion tools are written in Python 3. They are designed to be usable by themselves on the command line (with the proper dependencies installed.)
At the moment, Magic the Gathering lists can be parsed from Tappedout, and Pokemon TCG decklists exported from PTCGO or pokemoncard.io.

### Usage

The python files in ```assets/python/``` can be used via the command line if one wishes.
One must install Python 3.8 or higher and install dependencies for whatever tool is being used (they are listed in the source code, and are all obtainable with pip).
Each tool also has usage hints in the source that are printed when they're run with invalid arguments.

## Website

The website is intended to be merely an access point for the deck conversion tools.
It is written using PHP 8 and Symfony 6, using Doctrine for a teensy bit of MySQL.

If the website is live, you can probably find it at flyingcodfish.hopto.org.
