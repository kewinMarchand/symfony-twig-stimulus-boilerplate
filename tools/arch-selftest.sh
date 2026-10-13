#!/bin/sh
# Écrit une violation volontaire de chaque règle d'architecture à l'emplacement réel, exige que
# `make arch-deptrac` et `make arch-wrappers` échouent en les citant toutes, puis retire les fichiers.
# Une règle qu'aucun test ne viole peut être débranchée sans bruit.
set -u

FILES="src/Catalog/Domain/ArchSelftestDomainToInfrastructure.php
src/Catalog/Domain/ArchSelftestDomainToFramework.php
src/Blog/Domain/ArchSelftestModuleToModule.php
src/Shared/UI/ArchSelftestSharedToModule.php
src/Catalog/UI/ArchSelftestUiToInfrastructure.php
src/Catalog/UI/ArchSelftestUiToHttpClient.php
templates/home/_arch_selftest.html.twig
assets/controllers/arch_selftest_controller.js"

cleanup() {
    for file in $FILES; do rm -f "$file"; done
}
trap cleanup EXIT INT TERM

php_class() {
    # $1 fichier, $2 namespace, $3 classe, $4 dépendance interdite (nom pleinement qualifié)
    printf '%s\n' '<?php' '' 'declare(strict_types=1);' '' "namespace $2;" '' \
        "final class $3" '{' "    public function __construct(public \\$4 \$dependency)" '    {' '    }' '}' > "$1"
}

php_class src/Catalog/Domain/ArchSelftestDomainToInfrastructure.php 'App\Catalog\Domain' \
    ArchSelftestDomainToInfrastructure 'App\Catalog\Infrastructure\InMemory\InMemoryCatalogRepository'
php_class src/Catalog/Domain/ArchSelftestDomainToFramework.php 'App\Catalog\Domain' \
    ArchSelftestDomainToFramework 'Symfony\Component\HttpFoundation\Response'
php_class src/Blog/Domain/ArchSelftestModuleToModule.php 'App\Blog\Domain' \
    ArchSelftestModuleToModule 'App\Catalog\Domain\Product'
mkdir -p src/Shared/UI
php_class src/Shared/UI/ArchSelftestSharedToModule.php 'App\Shared\UI' \
    ArchSelftestSharedToModule 'App\Catalog\Domain\CategoryTree'
php_class src/Catalog/UI/ArchSelftestUiToInfrastructure.php 'App\Catalog\UI' \
    ArchSelftestUiToInfrastructure 'App\Catalog\Infrastructure\InMemory\InMemoryCatalogRepository'
php_class src/Catalog/UI/ArchSelftestUiToHttpClient.php 'App\Catalog\UI' \
    ArchSelftestUiToHttpClient 'Symfony\Contracts\HttpClient\HttpClientInterface'
printf '%s\n' '<svg><use href="/icons.svg#x"></use></svg>' > templates/home/_arch_selftest.html.twig
printf '%s\n' "import EmblaCarousel from 'embla-carousel';" '' 'export default EmblaCarousel;' \
    > assets/controllers/arch_selftest_controller.js

failed=0
deptrac_output=$(make --no-print-directory arch-deptrac 2>&1) && failed=1
wrappers_output=$(make --no-print-directory arch-wrappers 2>&1) && failed=1
if [ "$failed" -ne 0 ]; then
    echo "ÉCHEC : une règle d'architecture accepte une violation volontaire, elle est débranchée."
    printf '%s\n%s\n' "$deptrac_output" "$wrappers_output"
    exit 1
fi

detected=0
for marker in ArchSelftestDomainToInfrastructure ArchSelftestDomainToFramework ArchSelftestModuleToModule \
    ArchSelftestSharedToModule ArchSelftestUiToInfrastructure ArchSelftestUiToHttpClient; do
    if printf '%s\n' "$deptrac_output" | grep -q "App.*\\\\$marker must not depend on"; then
        detected=$((detected + 1))
    else
        echo "ÉCHEC : deptrac ne signale pas $marker."
        failed=1
    fi
done
for marker in templates/home/_arch_selftest.html.twig assets/controllers/arch_selftest_controller.js; do
    if printf '%s\n' "$wrappers_output" | grep -q "^$marker:"; then
        detected=$((detected + 1))
    else
        echo "ÉCHEC : la règle des wrappers ne signale pas $marker."
        failed=1
    fi
done

if [ "$failed" -ne 0 ]; then
    printf '%s\n%s\n' "$deptrac_output" "$wrappers_output"
    exit 1
fi
echo "OK : $detected violations volontaires sur 8 détectées, fichiers retirés."
