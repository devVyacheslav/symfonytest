<?php

namespace MainBundle\Controller;

use MainBundle\C;
use MainBundle\Entity\Members;
use Symfony\Component\HttpFoundation\RedirectResponse;
use MainBundle\Utils\MyLogHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MembersController
 * @package MainBundle\Controller
 */
class MembersController extends GeneralController
{
    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function indexAction(Request $request)
    {
        $profileRoute = $this->get('auth_resolver')->getProfileRoute($request);
        $members = $this->get('model.members')->getMembers($this->getUser());


        $users = $this->get('model.user')->allUsersRegister();
        foreach ($users as $user=>$item){
//            MyLogHelper::lg($user);
                $member = $item->getGroups();
                $member = array_shift($member);
        }


        if (is_null($members)) {
            $this->addFlash(C::FLASH_ERROR, 'Невозможно загрузить личный кабинет.');

            return $this->redirectToRoute('default');
        }
        
        return $this->render("MainBundle:Members:index.html.twig", [
            'profile_route' => $profileRoute,
            'Members' => $members,
            'users' => $users
        ]);
    }

//    public function allUsersAction(Request $request)
//    {
//        $profileRoute = $this->get('auth_resolver')->getProfileRoute($request);
//        $Members = $this->get('model.Members')->getMembers($this->getUser());
//
//        $users = $this->get('model.user')->allUsersRegister();
//    }

    /**
     * @param Request $request
     * @return Response
     */
    public function createOrderAction(Request $request)
    {
        $services = $this->get('model.service')->getActiveServicesArray();
        $temples = $this->get('model.temple')->getActiveTemplesArray();

        $form = $this->createForm(CreateOrderType::class, null, ['services' => $services, 'temples' => $temples]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $data = $form->getData();

                try {
                    // TODO: Use $groupId to create link
                    $groupId = $this->get('model.order')->createOrdersGroupFromForm(
                        $this->getUser(),
                        Members::class,
                        $data
                    );

                    $this->addFlash(C::FLASH_SUCCESS, 'Ваш заказ создан и находится в разделе "Мои заказы".');
                } catch (\Exception $e) {
                    $this->addFlash(C::FLASH_ERROR, 'В процессе оформления заказа возникла ошибка.');
                    $this->addFlash(C::FLASH_ERROR, $e->getMessage()); // TODO: delete!
                }
            } else {
                $this->addFlash(C::FLASH_ERROR, 'Проверьте правильность ввода данных!');
            }

            return $this->redirectToRoute('Members_create_order');
        }

        return $this->render("MainBundle:Members:createOrder.html.twig", [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function listOrdersAction(Request $request)
    {
        $modelOrder = $this->get('model.order');
        $Members = $this->get('model.Members')->getMembers($this->getUser());
        $orders = $modelOrder->getOrdersByParams($Members);

        // В фильтры выводятся только те храмы, услуги и статусы, которые фигурируют в заказах пользователя
        $orderFilters = $modelOrder->getSearchOrderFilters($orders);

        $formOptions = array_merge($orderFilters, ['method' => 'GET']);
        $formFactory = $this->get('form.factory');
        $form = $formFactory->createNamed('', OrderFiltersType::class, null, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $data = $form->getData();
            // FIXME: Могут ли фильтры быть мультиселектами??
            $temple = !is_null($data['temple'])
                ? $this->get('model.temple')->getTempleById($data['temple'])
                : null;
            $service = !is_null($data['service'])
                ? $this->get('model.service')->getServiceById($data['service'])
                : null;
            $orders = $modelOrder->getOrdersByParams($Members, $temple, $service, $data['order_state']);
        }        

        return $this->render("MainBundle:Members:listOrders.html.twig", [
            'orders' => $orders,
            'form' => $form->createView()
        ]);
    }
}