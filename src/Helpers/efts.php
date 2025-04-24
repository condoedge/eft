<?php 

/* ELEMENTS */
function _LabelTotalsEft($label, $total)
{
	return _FlexBetween(
        _Html($label),
        _Currency($total)->class('font-semibold ml-2'),
    );
}