<?php

namespace Tests\Controllers;

use Ladmin\Controllers\AdminController;
use Ladmin\Form;
use Ladmin\Grid;
use Tests\Models\Image;

class ImageController extends AdminController
{
    protected $title = 'Images';

    protected $description = [
        'create' => 'Images upload',
    ];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Image());

        $grid->id('ID')->sortable();

        $grid->created_at();
        $grid->updated_at();

        $grid->disableFilter();

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Image());

        $form->display('id', 'ID');

        $form->image('image1');
        $form->image('image2')->rotate(90);
        $form->image('image3')->flip('v');
        $form->image('image4')->move(null, 'renamed.jpeg');
        // Force jpeg extension to match test expectation
        $form->image('image5')->name(function ($file) {
            return 'asdasdasdasdasd.jpeg';
        });
        $form->image('image6')->uniqueName();

        $form->display('created_at', 'Created At');
        $form->display('updated_at', 'Updated At');

        return $form;
    }
}
