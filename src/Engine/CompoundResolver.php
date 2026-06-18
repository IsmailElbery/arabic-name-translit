<?php

declare(strict_types=1);

namespace Ismail\NameTranslit\Engine;

use Ismail\NameTranslit\Standards\StandardInterface;
use Ismail\NameTranslit\Support\Diacritics;

/**
 * Step 2 of the pipeline: split a name on particles (عبد، أبو، ابن، بن، بنت، آل)
 * and the definite article ال, re-resolve each component through the
 * dictionary (falling back to the rule engine per component), then join per
 * the active standard's rules.
 *
 * Returns null when the input is not actually a compound — a single bare
 * token with no article and no particle — so the pipeline can fall through to
 * the rule engine.
 *
 * @see CLAUDE.md §3
 */
final class CompoundResolver
{
    /**
     * Particle markers keyed by NORMALIZED Arabic (so أبو is stored as ابو,
     * and the family particle آل as the standalone token ال).
     *
     * @var array<string, array{render: string, absorbs_article?: bool}>|null
     */
    private ?array $particles = null;

    public function __construct(
        private readonly DictionaryResolver $dictionary,
        private readonly RuleEngine $ruleEngine,
        private readonly string $dataDir,
    ) {
    }

    public function resolve(string $normalized, StandardInterface $standard): ?string
    {
        $tokens = $normalized === '' ? [] : explode(' ', $normalized);

        if (! $this->isCompound($tokens)) {
            return null;
        }

        $particles = $this->particles();
        $parts = [];
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            if (isset($particles[$token])) {
                $particle = $particles[$token];
                $parts[] = $standard->postProcess($particle['render']);

                // e.g. عبد absorbs the article of the following word so that
                // عبد الرحمن -> Abdul Rahman / ABDULRAHMAN (not Abdul Al Rahman).
                if (! empty($particle['absorbs_article'])
                    && isset($tokens[$i + 1])
                    && Diacritics::hasDefiniteArticle($tokens[$i + 1])
                ) {
                    $tokens[$i + 1] = Diacritics::stripDefiniteArticle($tokens[$i + 1]);
                }

                continue;
            }

            $parts[] = $this->resolveComponent($token, $standard);
        }

        return $standard->joinCompound($parts);
    }

    private function resolveComponent(string $token, StandardInterface $standard): string
    {
        $hadArticle = Diacritics::hasDefiniteArticle($token);
        $base = $hadArticle ? Diacritics::stripDefiniteArticle($token) : $token;

        $resolved = $this->dictionary->resolve($base, $standard)
            ?? $this->ruleEngine->transliterate($base, $standard);

        if ($hadArticle) {
            $article = $standard->definiteArticle();
            if ($article !== '') {
                $resolved = $standard->postProcess($article) . $resolved;
            }
        }

        return $resolved;
    }

    /**
     * @param list<string> $tokens
     */
    private function isCompound(array $tokens): bool
    {
        if (count($tokens) > 1) {
            return true;
        }

        if ($tokens === []) {
            return false;
        }

        $only = $tokens[0];

        return isset($this->particles()[$only]) || Diacritics::hasDefiniteArticle($only);
    }

    /**
     * @return array<string, array{render: string, absorbs_article?: bool}>
     */
    private function particles(): array
    {
        if ($this->particles !== null) {
            return $this->particles;
        }

        $path = $this->dataDir . DIRECTORY_SEPARATOR . 'particles.php';

        /** @var array<string, array{render: string, absorbs_article?: bool}> $data */
        $data = is_file($path) ? require $path : [];

        return $this->particles = $data;
    }
}
