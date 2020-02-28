<?php

/**
 * CRUD construction base controller for models quickly
 *
 * @category Kumbia
 * @package Controller
 */
class ScaffoldController extends AdminController
{
    /** @var string Folder in views/_shared/scaffolds/ */
    public $scaffold = 'kumbia';
    /** @var string Model name in CamelCase*/
    public $model = '';

    /**
     * Paginated Results
     * 
     * @param int $page   Page to display
     */
    public function index($page = 1)
    {
        $this->data = (new $this->model)->paginate("page: $page", 'order: id desc');
    }

    /**
     * Create a Record
     */
    public function create()
    {
        if (Input::hasPost($this->model)) {

            $obj = new $this->model;
            //In case the save operation fails
            if (!$obj->save(Input::post($this->model))) {
                Flash::error('Operation Failed');
                //the data on the form becomes persistent
                $this->{$this->model} = $obj;
                return;
            }
            return Redirect::to();
        }
        // It is only necessary for the autoForm
        $this->{$this->model} = new $this->model;
    }

    /**
     * Edit a Record
     * 
     * @param int $id  Registry ID
     */
    public function edit($id)
    {
        View::select('create');

        //it is verified if the data has been sent via POST
        if (Input::hasPost($this->model)) {
            $obj = new $this->model;
            if (!$obj->update(Input::post($this->model))) {
                Flash::error('Operation Failed');
                //the data on the form becomes persistent
                $this->{$this->model} = Input::post($this->model);
            } else {
                return Redirect::to();
            }
        }

        //Applying object autoload, to start editing
        $this->{$this->model} = (new $this->model)->find((int) $id);
    }

    /**
     * Delete a Record
     * 
     * @param int $id Record identifier
     */
    public function delete($id)
    {
        if (!(new $this->model)->delete((int) $id)) {
            Flash::error('Operation Failed');
        }
        //routing to the index to list the articles
        Redirect::to();
    }

    /**
     * See a Record
     * 
     * @param int $id Record identifier
     */
    public function view($id)
    {
        $this->data = (new $this->model)->find_first((int) $id);
    }
}
