# Contributing

## Checks

Before opening a pull request, run:

- `composer test`
- `composer analyse`
- `composer lint`

`composer lint` checks code style and Rector rules. `composer cs` and `composer rector` fix what
it finds.

A change to configuration or behaviour also updates the pages under `docs/`.

## Pull requests

Titles follow `type(Scope): subject`, for example `chore(CI): run the build workflow weekly`.
`type` is one of `feat`, `fix`, `docs`, `test`, `chore` or `refactor`. The scope is the class or
area the change is about. Pull requests are squash-merged, so the title becomes the commit
subject on `main`.

The description has an **Overview**, saying why the change exists - the problem, not the
implementation - and a **Changes** list of what moved, one line each. Class names, methods and
other identifiers go in backticks.
