<?php

/**
 * This file is part of the Arachne
 *
 * Copyright (c) Jáchym Toušek (enumag@gmail.com)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Arachne\Forms\Extension\ReadOnlyCollection\EventListener;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Jáchym Toušek <enumag@gmail.com>
 */
class ReadOnlyCollectionListener implements EventSubscriberInterface
{

	public static function getSubscribedEvents()
	{
		return [
			FormEvents::PRE_SET_DATA => 'preSetData',
		];
	}

	public function preSetData(FormEvent $event)
	{
		$data = $event->getData();

		if (null === $data) {
			$data = array();
		}

		if (!is_array($data) && !($data instanceof \Traversable && $data instanceof \ArrayAccess)) {
			throw new UnexpectedTypeException($data, 'array or (\Traversable and \ArrayAccess)');
		}

		if (!is_array($data)) {
			$data = iterator_to_array($data);
		}

		$event->setData($data);
	}

}
