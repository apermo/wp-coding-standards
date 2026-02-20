<?php
// phpcs:disable WordPress,Squiz,PSR1,Universal.UseStatements,Apermo
// phpcs:disable SlevomatCodingStandard.Namespaces
// phpcs:disable SlevomatCodingStandard.TypeHints.PropertyTypeHint
// phpcs:disable SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
// phpcs:disable Generic

class TypeHintFixture {
	// Missing return type — should be flagged (line 10).
	public function no_return_type() {
		return 1;
	}

	// Missing parameter type — should be flagged (line 15).
	// phpcs:disable SlevomatCodingStandard.TypeHints.ReturnTypeHint
	public function no_param_type( $value ): int {
		return (int) $value;
	}
}
