<?php 

/* ELEMENTS */
function _LabelTotalsEft($label, $total)
{
	return _FlexBetween(
        _Html($label)->class('font-semibold'),
        _Currency($total)->class('ml-2'),
    );
}