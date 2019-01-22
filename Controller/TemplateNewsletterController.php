<?php

namespace FAC\EmailBundle\Controller;

use FAC\EmailBundle\Entity\TemplateNewsletter;
use FAC\EmailBundle\Form\TemplateNewsletterType;
use FOS\RestBundle\Controller\FOSRestController;
use FAC\EmailBundle\Service\TemplateNewsletterService;
use Exceptions\ValidationException;
use FOS\RestBundle\Controller\Annotations as Rest;
use LogBundle\Service\LogMonitorService;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Utils\IntegerUtils;
use Utils\ResponseUtils;

class TemplateNewsletterController extends FOSRestController {

    /**
     * Get a list of all template newsletter.
     *
     * @Rest\Get("/admin/template-newsletter/list")
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
     *         @Model(type=TemplateNewsletter::class)
     *     )
     * ),
     * @SWG\Tag(name="TemplateNewsletter")
     * @param   TemplateNewsletterService $templateNewsletterService
     * @param   LogMonitorService $logMonitorService
     * @return  JsonResponse
     */
    public function listAction(TemplateNewsletterService $templateNewsletterService, LogMonitorService $logMonitorService) {
        $response = new ResponseUtils($this->get("translator"), $logMonitorService);

        $templateNewsletters = $templateNewsletterService->getList('adminSerializer', array("isDisable"=>'0'));

        return $response->getListResponse($templateNewsletters);
    }

    /**
     * Action used to get a TemplateNewsletter.
     *
     * @Rest\Get("/admin/template-newsletter/{id}")
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
     *     description="No TemplateNewsletter found.",
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
     *         @Model(type=TemplateNewsletter::class)
     *     )
     * ),
     * @SWG\Tag(name="TemplateNewsletter")
     *
     * @param  TemplateNewsletter $templateNewsletter
     * @param  integer $id
     * @param  LogMonitorService $logMonitorService
     * @return JsonResponse
     */
    public function showAction(TemplateNewsletter $templateNewsletter=null, $id, LogMonitorService $logMonitorService){
        $response = new ResponseUtils($this->get("translator"), $logMonitorService);

        if(!IntegerUtils::checkId($id)){
            return $response->getResponse(array(), "parameter.id.invalid",400);
        }

        if(is_null($templateNewsletter)){
            return $response->getResponse(array(), "data.not.found.404",404);
        }

        return $response->getResponse($templateNewsletter->adminSerializer());
    }

    /**
     * Create a templateNewsletter.
     *
     * @Rest\Post("super/template-newsletter")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token: Bearer <token>"
     * ),
     * @SWG\Parameter(
     *     name="TemplateNewsletter fields",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=TemplateNewsletterType::class)
     *     ),
     *     description="TemplateNewsletterType fields"
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
     *         @Model(type=TemplateNewsletter::class)
     *     )
     * ),
     * @SWG\Tag(name="TemplateNewsletter")
     * @param   Request $request
     * @param   TemplateNewsletterService $templateNewsletterService
     * @param   ValidationException $validationException
     * @param   LogMonitorService $logMonitorService
     * @return  JsonResponse
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function createAction(Request $request, TemplateNewsletterService $templateNewsletterService, ValidationException $validationException, LogMonitorService $logMonitorService) {
        $response  = new ResponseUtils($this->get("translator"), $logMonitorService);

        $templateNewsletter    = new TemplateNewsletter();
        $validator = $this->get('validator');

        $form = $this->createForm(TemplateNewsletterType::class, $templateNewsletter);
        $data = json_decode($request->getContent(), true);

        $form->submit($data);
        $errors = $validator->validate($templateNewsletter);
        if(count($errors) > 0) {
            $formattedErrors = $validationException->getFormattedExceptions($errors);
            return $response->getResponse($formattedErrors, "parameters.invalid",400);
        }

        if(!$templateNewsletterService->save($templateNewsletter, $this->getUser())) {
            return $response->getResponse(array(), "error.save", 500);
        }

        return $response->getResponse($templateNewsletter->adminSerializer(), "success.save", 201);
    }

    /**
     * Update a templateNewsletter.
     *
     * @Rest\Put("/super/template-newsletter/{id}")
     *
     * @SWG\Parameter(
     *     name="Authorization",
     *     in="header",
     *     required=true,
     *     type="string",
     *     description="Authorization token: Bearer <token>"
     * ),
     * @SWG\Parameter(
     *     name="TemplateNewsletter fields",
     *     in="body",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=TemplateNewsletterType::class)
     *     ),
     *     description="TemplateNewsletterType fields"
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
     *         @Model(type=TemplateNewsletter::class)
     *     )
     * ),
     * @SWG\Tag(name="TemplateNewsletter")
     * @param   TemplateNewsletter|null $templateNewsletter
     * @param   int $id
     * @param   Request $request
     * @param   TemplateNewsletterService $templateNewsletterService
     * @param   ValidationException $validationException
     * @param   LogMonitorService $logMonitorService
     * @return  JsonResponse
     * @throws  \Doctrine\DBAL\ConnectionException
     */
    public function updateAction(TemplateNewsletter $templateNewsletter=null, $id, Request $request, TemplateNewsletterService $templateNewsletterService, ValidationException $validationException, LogMonitorService $logMonitorService) {
        $response  = new ResponseUtils($this->get("translator"), $logMonitorService);

        if(!IntegerUtils::checkId($id)){
            return $response->getResponse(array(), "parameter.id.invalid",400);
        }

        if(is_null($templateNewsletter)){
            return $response->getResponse(array(), "data.not.found.404",404);
        }

        $validator = $this->get('validator');

        $form = $this->createForm(TemplateNewsletterType::class, $templateNewsletter);
        $data = json_decode($request->getContent(), true);

        $form->submit($data);
        $errors = $validator->validate($templateNewsletter);

        if(count($errors) > 0) {
            $formattedErrors = $validationException->getFormattedExceptions($errors);
            return $response->getResponse($formattedErrors, "parameters.invalid",400);
        }

        if(!$templateNewsletterService->save($templateNewsletter)) {
            return $response->getResponse(array(), "error.save",500);
        }

        return $response->getResponse($templateNewsletter->adminSerializer(), "success.save");
    }

    /**
     * Delete a templateNewsletter.
     *
     * @Rest\Delete("/super/template-newsletter/{id}")
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
     *         @Model(type=TemplateNewsletter::class)
     *     )
     * ),
     * @SWG\Tag(name="TemplateNewsletter")
     * @param   TemplateNewsletter|null $templateNewsletter
     * @param   int $id
     * @param   TemplateNewsletterService $templateNewsletterService
     * @param   LogMonitorService $logMonitorService
     * @return  JsonResponse
     * @throws  \Doctrine\DBAL\ConnectionException
     */
    public function deleteAction(TemplateNewsletter $templateNewsletter=null, $id, TemplateNewsletterService $templateNewsletterService, LogMonitorService $logMonitorService) {
        $response  = new ResponseUtils($this->get("translator"), $logMonitorService);

        if(!IntegerUtils::checkId($id)){
            return $response->getResponse(array(), "parameter.id.invalid",400);
        }

        if(is_null($templateNewsletter)){
            return $response->getResponse(array(), "data.not.found.404",404);
        }

        $user = $this->getUser();
        if(!$user) {
            return $response->getResponse(array(), "user.inexistent", 400);
        }

        if(!$templateNewsletterService->delete($templateNewsletter, $user)) {
            return $response->getResponse(array(), "error.save",500);
        }

        return $response->getResponse(array(),"success.delete");
    }
}
