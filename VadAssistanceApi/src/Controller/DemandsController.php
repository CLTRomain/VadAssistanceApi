<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Demands Controller
 *
 * @property \App\Model\Table\DemandsTable $Demands
 */
class DemandsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->Demands->find();
        $demands = $this->paginate($query);

        $this->set(compact('demands'));
    }

    /**
     * View method
     *
     * @param string|null $id Demand id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $demand = $this->Demands->get($id, contain: []);
        $this->set(compact('demand'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $demand = $this->Demands->newEmptyEntity();
        if ($this->request->is('post')) {
            $demand = $this->Demands->patchEntity($demand, $this->request->getData());
            if ($this->Demands->save($demand)) {
                $this->Flash->success(__('The demand has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The demand could not be saved. Please, try again.'));
        }
        $this->set(compact('demand'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Demand id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $demand = $this->Demands->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $demand = $this->Demands->patchEntity($demand, $this->request->getData());
            if ($this->Demands->save($demand)) {
                $this->Flash->success(__('The demand has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The demand could not be saved. Please, try again.'));
        }
        $this->set(compact('demand'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Demand id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $demand = $this->Demands->get($id);
        if ($this->Demands->delete($demand)) {
            $this->Flash->success(__('The demand has been deleted.'));
        } else {
            $this->Flash->error(__('The demand could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
