# Symfony 7.4 form validation groups reproducer

Minimal Symfony application for reproducing a form validation issue with a
dynamic `validation_groups` callable.

## What is included

- `src/Form/BugReproType.php` – a form with two fields:
  - `number` (`NumberType`)
  - `text` (`TextType`)
- The form uses a `validation_groups` callable. It returns the custom
  `with_number` group only when the submitted `number` value is not `null`;
  otherwise it returns an empty array so validation is skipped.
- `tests/Form/BugReproTypeTest.php` – PHPUnit tests that submit the form and
  assert the resulting errors.
- `Dockerfile` – builds the project and runs PHPUnit.

## Versions

The project is pinned to Symfony `7.4.14`:

- `symfony/framework-bundle`: `7.4.14`
- `symfony/validator`: `7.4.14`
- `symfony/form`: `7.4.14`

Other Symfony packages resolve to their latest available `7.4.x` release in
`composer.lock`. `symfony/yaml` stays on `7.4.22` because `7.4.14` is blocked
by a security advisory; it is not involved in the form validation behavior.

## Expected behavior

| number | text | validation groups | result |
| --- | --- | --- | --- |
| `null` / empty | empty | skipped | no errors |
| `42` | empty | `with_number` | one `NotBlank` error on `text` |
| `42` | `hello` | `with_number` | no errors |
| `invalid` | empty | callable returns `[]` | expected: form invalid with a transformation error |

The `invalid` case reproduces the bug on Symfony 7.4.14: the `number` field has
a `TransformationFailedException`, but the form reports no errors and
`isValid()` returns `true`. Because the failed transformation leaves the
number's model data as `null`, the callable returns `[]`, and the form
validator skips validation before it can add the transformation failure error.

## Run with Docker

```bash
docker build -t symfony-form-bug-repro .
docker run --rm symfony-form-bug-repro
```

## Run locally

```bash
composer install
vendor/bin/phpunit
```
