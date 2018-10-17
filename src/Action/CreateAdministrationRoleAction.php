<?php

declare(strict_types=1);

namespace Sylius\RbacPlugin\Action;

use Prooph\ServiceBus\CommandBus;
use Prooph\ServiceBus\Exception\CommandDispatchException;
use Sylius\RbacPlugin\Creator\CommandCreatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Webmozart\Assert\Assert;

final class CreateAdministrationRoleAction
{
    /** @var CommandBus */
    private $commandBus;

    /** @var CommandCreatorInterface */
    private $createAdministrationRoleCommandCreator;

    /** @var Session */
    private $session;

    /** @var UrlGeneratorInterface */
    private $router;

    public function __construct(
        CommandBus $commandBus,
        CommandCreatorInterface $createAdministrationRoleCommandCreator,
        Session $session,
        UrlGeneratorInterface $router
    ) {
        $this->commandBus = $commandBus;
        $this->createAdministrationRoleCommandCreator = $createAdministrationRoleCommandCreator;
        $this->session = $session;
        $this->router = $router;
    }

    public function __invoke(Request $request)
    {
        try {
            $this->commandBus->dispatch($this->createAdministrationRoleCommandCreator->fromRequest($request));

            $this->session->getFlashBag()->add(
                'success',
                'sylius_rbac.administration_role_added_successfully'
            );
        } catch (CommandDispatchException $exception) {
            Assert::notNull($exception->getPrevious());
            $this->session->getFlashBag()->add('error', $exception->getPrevious()->getMessage());
        } catch (\InvalidArgumentException $exception) {
            $this->session->getFlashBag()->add('error', $exception->getMessage());
        }

        return new RedirectResponse($this->router->generate('sylius_rbac_administration_role_new', []));
    }
}
