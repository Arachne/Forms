<?php

namespace Arachne\Forms\Extension\Application;

use Arachne\Forms\Exception\InvalidArgumentException;
use Nette\Application\Request;
use Nette\Http\FileUpload;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\RequestHandlerInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ApplicationRequestHandler implements RequestHandlerInterface
{
    /**
     * @param FormInterface $form
     * @param Request       $request
     */
    public function handleRequest(FormInterface $form, $request = null)
    {
        if (!$request instanceof Request) {
            throw new UnexpectedTypeException($request, Request::class);
        }

        $name = $form->getName();

        if ($name === '') {
            throw new InvalidArgumentException('Forms are not allowed to have an emtpy string as name.');
        }

        $method = $form->getConfig()->getMethod();

        if ($method !== $request->getMethod()) {
            return;
        }

        if ($method === 'GET') {
            $get = $request->getParameters();

            // Don't submit GET requests if the form's name does not exist in the request.
            if (!isset($get[$name])) {
                return;
            }

            $data = $get[$name];
        } else {
            $post = $request->getPost();
            $files = $request->getFiles();
            $default = $form->getConfig()->getCompound() ? [] : null;

            $postData = isset($post[$name]) ? $post[$name] : $default;
            $filesData = isset($files[$name]) ? $files[$name] : $default;

            if (is_array($postData) && is_array($filesData)) {
                $data = array_replace_recursive($postData, $filesData);
            } else {
                $data = $postData ?: $filesData;
            }

            // Don't submit the form if it is not present in the request.
            if (!$data) {
                return;
            }
        }

        $form->submit($data, $method !== 'PATCH');
    }

    public function isFileUpload($data)
    {
        return $data instanceof FileUpload;
    }
}
