# Doctrine Doctor Runtime Optimization

Doctrine Doctor is enabled only in the Symfony dev environment.

## How To Use

1. Start the app in `dev`.
2. Open a page that uses Doctrine queries.
3. Open the Symfony web profiler toolbar.
4. Select the Doctrine Doctor panel.
5. Check warnings for N+1 queries, slow queries, missing indexes, and hydration issues.

## Current Configuration

- Toolbar panel is visible in `dev`.
- Internal debug details are disabled.
- Query backtraces are enabled through Doctrine DBAL dev config.
- N+1 detection threshold is set to 3 repeated queries.
- Slow query threshold remains 100 ms.
- Missing-index analysis is enabled for slow queries.

## Recommended Workflow

- Use profiler findings before changing repositories or controller query shapes.
- Fix one page/workflow at a time, then refresh the profiler to confirm query count and timing improved.
- Prefer repository methods with explicit joins and `addSelect()` when Doctrine Doctor reports lazy-loading N+1 behavior.
- Keep migration/schema changes separate from this static-analysis setup.

## Applied Query Optimizations

- Farmer dashboard document counters now use SQL aggregate counts instead of hydrating every document.
- Product offer counts for admin/API/export views now use one grouped count query instead of per-product lazy collection counts.
- Active offer lists now eager-load their financial product to avoid N+1 access in Twig/API/PDF output.
- Chatbot product/offer helpers now use limited and name-specific repository queries instead of loading full tables and slicing in PHP.
- Product rate distribution now aggregates ranges in SQL instead of hydrating all product rates.
