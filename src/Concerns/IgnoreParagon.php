<?php

namespace Kirschbaum\Paragon\Concerns;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class IgnoreParagon {}
