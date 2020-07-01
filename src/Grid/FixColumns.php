<?php

namespace Dcat\Admin\Grid;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Illuminate\Support\Collection;

class FixColumns
{
    /**
     * @var Grid
     */
    protected $grid;

    /**
     * @var int
     */
    public $head;

    /**
     * @var int
     */
    public $tail;

    /**
     * @var Collection
     */
    protected $left;

    /**
     * @var Collection
     */
    protected $right;

    /**
     * @var string
     */
    protected $view = 'admin::grid.fixed-table';

    /**
     * FixColumns constructor.
     *
     * @param Grid $grid
     * @param int  $head
     * @param int  $tail
     */
    public function __construct(Grid $grid, $head, $tail = -1)
    {
        $this->grid = $grid;
        $this->head = $head;
        $this->tail = $tail;

        $this->left = Collection::make();
        $this->right = Collection::make();
    }

    /**
     * @return Collection
     */
    public function leftColumns()
    {
        return $this->left;
    }

    /**
     * @return Collection
     */
    public function rightColumns()
    {
        return $this->right;
    }

    /**
     * @return \Closure
     */
    public function apply()
    {
        $this->grid->view($this->view);

        if ($this->head > 0) {
            $this->left = $this->grid->columns()->slice(0, $this->head);
        }

        if ($this->tail < 0) {
            $this->right = $this->grid->columns()->slice($this->tail);
        }

        $this->addStyle();
        $this->addScript();
    }

    /**
     * @return $this
     */
    protected function addScript()
    {
        $script = <<<JS

(function () {
    var theadHeight = $('.table-main thead tr').outerHeight();
    $('.table-fixed thead tr').outerHeight(theadHeight);
    
    var tfootHeight = $('.table-main tfoot tr').outerHeight();
    $('.table-fixed tfoot tr').outerHeight(tfootHeight);
    
    $('.table-main tbody tr').each(function(i, obj) {
        var height = $(obj).outerHeight();

        $('.table-fixed-left tbody tr').eq(i).outerHeight(height);
        $('.table-fixed-right tbody tr').eq(i).outerHeight(height);
    });
    
    if ($('.table-main').width() >= $('.table-main').prop('scrollWidth')) {
        $('.table-fixed').hide();
    } else {
        var height = ($(window).height() - 200);
        
        $('.table-main,.table-fixed').css({height: height + 'px'});
        $('.table-fixed-right').css({right: '15px'});
        $('.table-fixed-right,.table-fixed-left').css({height: (height - 16) + 'px'});
        
        $('.table-main').scroll(function () {
            var self = $(this); 
            
            self.parents('.tables-container').find('.table-fixed-right,.table-fixed-left').scrollTop(self.scrollTop());
        });
    }
    
    $('.table-wrap tbody tr').on('mouseover', function () {
        var index = $(this).index();
        $('.table-main tbody tr').eq(index).addClass('active');
        $('.table-fixed-left tbody tr').eq(index).addClass('active');
        $('.table-fixed-right tbody tr').eq(index).addClass('active');
    });
    
    $('.table-wrap tbody tr').on('mouseout', function () {
        var index = $(this).index();
        
        $('.table-main tbody tr').eq(index).removeClass('active');
        $('.table-fixed-left tbody tr').eq(index).removeClass('active');
        $('.table-fixed-right tbody tr').eq(index).removeClass('active');
    });
})();

JS;

        Admin::script($script, true);

        return $this;
    }

    /**
     * @return $this
     */
    protected function addStyle()
    {
        $style = <<<'CSS'
.tables-container {
    position:relative;
    margin-top: 12px;
}

.tables-container table {
    margin-bottom: 0 !important;
}

.tables-container table th, .tables-container table td {
    white-space:nowrap;
}

.table-wrap table tr .active {
    background: #f5f5f5;
}

.table-main {
    overflow: auto;
    width: 100%;
}

.table-fixed {
    position:absolute;
	top: 0;
	z-index:10;
	overflow: hidden;
}

.table-fixed th {
    background: #eff3f8;
}

.table-fixed-left {
	left:0;
}

.table-fixed-right {
	right:0;
}

.table-fixed-left {
    box-shadow: 5px 0 5px -5px rgba(0,0,0,.1);
}

.table-fixed-right {
    box-shadow: -5px 0 5px -5px rgba(0,0,0,.1);
}

.tables-container .table.table-bordered.dataTable.complex-headers {
    margin-top: 0!important;
}
CSS;

        Admin::style($style);

        return $this;
    }
}
