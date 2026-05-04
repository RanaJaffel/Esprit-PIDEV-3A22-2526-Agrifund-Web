# PHPStan Level 8 Report

Generated from:

```bash
vendor/bin/phpstan analyse --configuration=phpstan.level8.neon --no-progress --error-format=json
```

## Current Result

- File errors: 714
- General errors: 0
- Status: staged debt; `composer phpstan:8` is expected to fail until these are fixed.

## Top Error Identifiers

| Count | Identifier |
| ---: | --- |
| 191 | `missingType.iterableValue` |
| 157 | `method.nonObject` |
| 130 | `argument.type` |
| 120 | `method.notFound` |
| 67 | `missingType.generics` |
| 9 | `return.type` |
| 7 | `method.unused` |
| 6 | `nullCoalesce.expr` |
| 4 | `arguments.count` |
| 4 | `missingType.return` |

## Hottest Files

| Count | File |
| ---: | --- |
| 38 | `src/Controller/Banque/BanqueMessagerieController.php` |
| 38 | `src/Controller/Admin/AdminMessagerieController.php` |
| 36 | `src/Controller/Agriculteur/AgriculteurMessagerieController.php` |
| 28 | `src/Controller/ReleveTerrainController.php` |
| 22 | `src/Service/IrrigationAIService.php` |
| 19 | `src/Service/HistoriqueService.php` |
| 15 | `src/Controller/Agriculteur/AgriculteurProfileController.php` |
| 15 | `src/Service/BlockchainService.php` |
| 14 | `src/Repository/ReleveTerrainRepository.php` |
| 13 | `src/Service/Application/ApplicationNotificationService.php` |

## Cleanup Order

1. Add array value types to public service/repository/controller return PHPDocs.
2. Add Doctrine collection generics on entity collection properties and accessors.
3. Narrow nullable values before method calls, especially current user and repository results.
4. Fix remaining return and argument type mismatches once the broad missing-type noise is reduced.
5. Re-run `composer phpstan:8` only after level 5 is clean.
