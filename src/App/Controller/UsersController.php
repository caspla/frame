<?php
namespace App\Controller;

use Core\Controller\BaseController;
use App\Model\User;

class UsersController extends BaseController
{
    public function listAction()
    {
        return $this->render('users/list.phtml', [
            'users' => User::findAll(null, 'lastname'),
        ]);
    }

    public function createAction()
    {
        return $this->render('users/create.phtml');
    }

    public function editAction($id)
    {
       $user = User::findById($id);
       if (!$user) $this->redirect('/users');

       return $this->render('users/edit.phtml', [
           'user' => $user,
       ]);
    }

    public function saveAction()
    {
        if ($_POST)
        {
            if (isset($_POST['userid']) && $_POST['userid']) $user = User::findById($_POST['userid']);
            else $user = new User;

            $user->firstname = $_POST['firstname'];
            $user->lastname = $_POST['lastname'];
            $user->email = $_POST['email'];
            $user->save();

            $this->redirect('/users');
        }
    }
}