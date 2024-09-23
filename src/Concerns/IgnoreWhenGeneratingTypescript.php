<?php

namespace Kirschbaum\Paragon\Concerns;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class IgnoreWhenGeneratingTypescript {}
