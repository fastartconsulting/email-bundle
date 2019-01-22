<?php

namespace FAC\EmailBundle\Controller;

use BadgeBundle\Entity\BadgeStructure;
use BadgeBundle\Service\BadgeStructureService;
use FAC\EmailBundle\Entity\Newsletter;
use FAC\EmailBundle\Form\NewsletterType;
use FOS\RestBundle\Controller\FOSRestController;
use FAC\EmailBundle\Service\NewsletterService;
use Exceptions\ValidationException;
use FOS\RestBundle\Controller\Annotations as Rest;
use LogBundle\Service\LogMonitorService;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Utils\IntegerUtils;
use Utils\ResponseUtils;

class NewsletterController extends FOSRestController {

    /**
     * Get a list of all articles.
     *
     * @Rest\Get("/admin/newsletter/list")
     *
     * @SWG\Response(
     *     response=400,
     *     description="The suggest is not valid.",
     * ),
     * @SWG\Response(
     *     response=403,
     *     description="The user has not rights to read.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Newsletter::class)
     *     )
     * ),
     * @SWG\Tag(name="Newsletter")
     * @param   NewsletterService $newsletterService
     * @param   LogMonitorService $logMonitorService
     * @return  JsonResponse
     */
    public function listAction(NewsletterService $newsletterService, LogMonitorService $logMonitorService) {
        $response = new ResponseUtils($this->get("translator"), $logMonitorService);

        $newsletters = $newsletterService->getList('adminSerializer', array("isDisable"=>'0'));

        return $response->getListResponse($newsletters);
    }

    /**
     * Action used to get a Newsletter.
     *
     * @Rest\Get("/admin/newsletter/{id}")
     *
     * @SWG\Response(
     *     response=400,
     *     description="The parameter is empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=401,
     *     description="The user is unauthorized.",
     * ),
     * @SWG\Response(
     *     response=403,
     *     description="The user is not authenticated.",
     * ),
     * @SWG\Response(
     *     response=404,
     *     description="No Newsletter found.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Server error occurred.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Newsletter::class)
     *     )
     * ),
     * @SWG\Tag(name="Newsletter")
     *
     * @param  Newsletter $newsletter
     * @param  integer $id
     * @param  LogMonitorService $logMonitorService
     * @return JsonResponse
     */
    public function showAction(Newsletter $newsletter=null, $id, LogMonitorService $logMonitorService){
        $response = new ResponseUtils($this->get("translator"), $logMonitorService);

        if(!IntegerUtils::checkId($id)){
            return $response->getResponse(array(), "parameter.id.invalid",400);
        }

        if(is_null($newsletter)){
            return $response->getResponse(array(), "data.not.found.404",404);
        }

        return $response->getResponse($newsletter->adminSerializer());
    }

    /**
     * Create a newsletter.
     *
     * @Rest\Post("super/newsletter")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token: Bearer <token>"
     * ),
     * @SWG\Parameter(
     *     name="Newsletter fields",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=NewsletterType::class)
     *     ),
     *     description="NewsletterType fields"
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="The parameters are empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error on saved occurred.",
     * ),
     * @SWG\Response(
     *     response=201,
     *     description="Success.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Newsletter::class)
     *     )
     * ),
     * @SWG\Tag(name="Newsletter")
     * @param   Request $request
     * @param   NewsletterService $newsletterService
     * @param BadgeStructureService $badgeStructureService
     * @param   ValidationException $validationException
     * @param   LogMonitorService $logMonitorService
     * @return  JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function createAction(Request $request, NewsletterService $newsletterService, BadgeStructureService $badgeStructureService, ValidationException $validationException, LogMonitorService $logMonitorService) {
        $response  = new ResponseUtils($this->get("translator"), $logMonitorService);

        $newsletter    = new Newsletter();
        $validator = $this->get('validator');

        $form = $this->createForm(NewsletterType::class, $newsletter);
        $data = json_decode($request->getContent(), true);

        $form->submit($data);
        $errors = $validator->validate($newsletter);
        if(count($errors) > 0) {
            $formattedErrors = $validationException->getFormattedExceptions($errors);
            return $response->getResponse($formattedErrors, "parameters.invalid",400);
        }

        //Control for mailing list on specific badge structure
        if($newsletter->getMailingListType() === Newsletter::MAILING_LIST_SPECIFIC_BADGE) {
            if(is_null($newsletter->getIdBadgeStructureMailingList())) {
                return $response->getResponse(array(), "parameters.invalid",400);
            }

            /** @var BadgeStructure $badgeStructure */
            $badgeStructure = $badgeStructureService->getById($newsletter->getIdBadgeStructureMailingList());
            if(is_null($badgeStructure)){
                return $response->getResponse(array(), "parameters.invalid",400);
            }
        }

        if(!$newsletterService->save($newsletter, $this->getUser())) {
            return $response->getResponse(array(), "error.save", 500);
        }

        return $response->getResponse($newsletter->adminSerializer(), "success.save", 201);
    }

    /**
     * Update a newsletter.
     *
     * @Rest\Put("/super/newsletter/{id}")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token: Bearer <token>"
     * ),
     * @SWG\Parameter(
     *     name="Newsletter fields",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=NewsletterType::class)
     *     ),
     *     description="NewsletterType fields"
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="The parameters are empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error on saved occurred.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Newsletter::class)
     *     )
     * ),
     * @SWG\Tag(name="Newsletter")
     * @param   Newsletter|null $newsletter
     * @param   int $id
     * @param   Request $request
     * @param   NewsletterService $newsletterService
     * @param   BadgeStructureService $badgeStructureService
     * @param   ValidationException $validationException
     * @param   LogMonitorService $logMonitorService
     * @return  JsonResponse
     * @throws  \Doctrine\DBAL\ConnectionException
     */
    public function updateAction(Newsletter $newsletter=null, $id, Request $request, NewsletterService $newsletterService, BadgeStructureService $badgeStructureService, ValidationException $validationException, LogMonitorService $logMonitorService) {
        $response  = new ResponseUtils($this->get("translator"), $logMonitorService);

        if(!IntegerUtils::checkId($id)){
            return $response->getResponse(array(), "parameter.id.invalid",400);
        }

        if(is_null($newsletter)){
            return $response->getResponse(array(), "data.not.found.404",404);
        }

        $validator = $this->get('validator');

        $form = $this->createForm(NewsletterType::class, $newsletter);
        $data = json_decode($request->getContent(), true);

        $form->submit($data);
        $errors = $validator->validate($newsletter);

        if(count($errors) > 0) {
            $formattedErrors = $validationException->getFormattedExceptions($errors);
            return $response->getResponse($formattedErrors, "parameters.invalid",400);
        }

        //Control for mailing list on specific badge structure
        if($newsletter->getMailingListType() === Newsletter::MAILING_LIST_SPECIFIC_BADGE) {
            if(is_null($newsletter->getIdBadgeStructureMailingList())) {
                return $response->getResponse(array(), "parameters.invalid",400);
            }

            /** @var BadgeStructure $badgeStructure */
            $badgeStructure = $badgeStructureService->getById($newsletter->getIdBadgeStructureMailingList());
            if(is_null($badgeStructure)){
                return $response->getResponse(array(), "parameters.invalid",400);
            }
        }

        if(!$newsletterService->save($newsletter)) {
            return $response->getResponse(array(), "error.save",500);
        }

        return $response->getResponse($newsletter->adminSerializer(), "success.save");
    }

    /**
     * Delete a newsletter.
     *
     * @Rest\Delete("/super/newsletter/{id}")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token: Bearer <token>"
     * ),
     * @SWG\Response(
     *     response=400,
     *     description="The parameters are empty or invalid.",
     * ),
     * @SWG\Response(
     *     response=500,
     *     description="Error on saved occurred.",
     * ),
     * @SWG\Response(
     *     response=200,
     *     description="Success.",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Newsletter::class)
     *     )
     * ),
     * @SWG\Tag(name="Newsletter")
     * @param   Newsletter|null $newsletter
     * @param   int $id
     * @param   NewsletterService $newsletterService
     * @param   LogMonitorService $logMonitorService
     * @return  JsonResponse
     * @throws  \Doctrine\DBAL\ConnectionException
     */
    public function deleteAction(Newsletter $newsletter=null, $id, NewsletterService $newsletterService, LogMonitorService $logMonitorService) {
        $response  = new ResponseUtils($this->get("translator"), $logMonitorService);

        if(!IntegerUtils::checkId($id)){
            return $response->getResponse(array(), "parameter.id.invalid",400);
        }

        if(is_null($newsletter)){
            return $response->getResponse(array(), "data.not.found.404",404);
        }

        $user = $this->getUser();
        if(!$user) {
            return $response->getResponse(array(), "user.inexistent", 400);
        }

        if(!$newsletterService->delete($newsletter, $user)) {
            return $response->getResponse(array(), "error.save",500);
        }

        return $response->getResponse(array(),"success.delete");
    }
}
