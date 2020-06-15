<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class CmUserAdmin
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class CmUserAdmin extends AbstractAdmin
{
    /**
     *
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * PartnerAdmin constructor.
     *
     * @param string                       $code
     * @param string                       $class
     * @param string                       $baseControllerName
     * @param UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct($code, $class, $baseControllerName, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;

        parent::__construct($code, $class, $baseControllerName);
    }

    /**
     * @return array
     */
    public function getExportFields()
    {
        return [
            'ID' => 'id',
            'Username' => 'username',
            'Channel Manager' => 'channelManager.identifier',
            'Created At' => 'createdAtFormatted',
            'Updated At' => 'updatedAtFormatted',
            'Password' => 'password',
        ];
    }

    /**
     *
     * @param DatagridMapper $datagridMapper
     *
     * @return void
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('username')
            ->add('channelManager.identifier', null, ['label' => 'Channel Manager'])
        ;
    }

    /**
     *
     * @param ListMapper $listMapper
     *
     * @return void
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('username')
            ->add('channelManager')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('_action', null, [
                'actions' => [
                    'show'   => [],
                    'edit'   => [],
                    'delete' => [],
                ],
            ]);
    }

    /**
     *
     * @param FormMapper $formMapper
     *
     * @return void
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('username')
            ->add('password', PasswordType::class, ['required' => false])
            ->add('channelManager');

        $formMapper
            ->getFormBuilder()
            ->addEventListener(
                FormEvents::PRE_SUBMIT,
                function (FormEvent $event) {
                    $data = $event->getData();
                    $user = $event->getForm()->getData();

                    if (!array_key_exists('password', $data) || empty($data['password'])) {
                        $data['password'] = $user->getPassword();
                        $event->setData($data);

                        return;
                    }

                    $data['password'] = $this->passwordEncoder->encodePassword(
                        $user,
                        $data['password']
                    );

                    $event->setData($data);
                }
            );
    }

    /**
     *
     * @param ShowMapper $showMapper
     *
     * @return void
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('id')
            ->add('username');
    }
}
