<!-- @if :var is 'var' -->
Shown if :var equals 'var'
<!-- @/if -->

<!-- @if :var_with_underscores is 'var_not_with_underscores' -->
Shown if :var_with_underscores is 'var_not_with_underscores'
<!-- @else -->
Else show if :var_with_underscores is not 'var_not_with_underscores'
<!-- @/if -->

<!-- @if :var_with_underscores is 'var_not_with_underscores' -->
Shown if :var_with_underscores is 'var_not_with_underscores'
<!-- @elseif :var_with_underscores is 'var_with_underscores' -->
Elseif show when :var_with_underscores is 'var_with_underscores'
<!-- @else -->
Something else
<!-- @/if -->