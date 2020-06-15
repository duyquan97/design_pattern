<?php

namespace App\Admin;

use App\Entity\CmUser;
use App\Model\CurrencyCode;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class PartnerAdmin
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class PartnerAdmin extends AbstractAdmin
{
    protected $baseRoutePattern = 'partner';

    /**
     *
     * @var array
     */
    protected $formOptions = [
        'validation_groups' => [
            'sonata',
        ],
    ];

    /**
     * @return array
     */
    public function getExportFields()
    {
        return [
            'ID' => 'id',
            'Partner URN' => 'identifier',
            'Partner Name' => 'name',
            'User' => 'user.username',
            'Channel Manager' => 'channelManager.identifier',
            'Enabled' => 'enabledString',
            'Currency' => 'currency',
            'Created At' => 'createdAtFormatted',
            'Updated At' => 'updatedAtFormatted',
            'Description' => 'description',
            'Status' => 'status',
            'Connected At' => 'connectedAt',
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
            ->add('identifier', null, ['label' => 'Partner URN', 'show_filter' => true])
            ->add('name', null, ['label' => 'Partner Name'])
            ->add('status')
            ->add('user')
            ->add('channelManager')
            ->add('enabled')
            ->add('currency')
            ->add('connectedAt')
            ->add('updatedAt');
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
            ->add('identifier', null, ['label' => 'Partner URN'])
            ->add('name', null, ['label' => 'Partner Name'])
            ->add('status', null, array(
                'template' => 'PartnerAdmin/partner_status.html.twig',
            ))
            ->add('channelManager')
            ->add('enabled')
            ->add('currency')
            ->add('createdAt')
            ->add('connectedAt')
            ->add(
                '_action',
                null,
                [
                    'actions' => [
                        'show'        => [],
                        'edit'        => [],
                        'refreshRoom' => [
                            'template' => 'PartnerAdmin/update_rooms_button.html.twig',
                        ],
                        'dataAlignment' => [
                            'template' => 'PartnerAdmin/data_alignment_button.html.twig',
                        ],
                        'delete'      => [],
                    ],
                ]
            );
    }

    /**
     *
     * @param FormMapper $formMapper
     *
     * @return void
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $partner = $this->getSubject();
        $disabled = $partner->getId() ? true : false;

        $formMapper
            ->add('identifier', null, [
                'disabled' => $disabled,
                'label'    => 'Partner URN',
            ])
            ->add('name', null, ['label' => 'Partner Name'])
            ->add('channelManager')
            ->add('currency', ChoiceType::class, [
                'choices' => [
                    CurrencyCode::SEK => CurrencyCode::SEK,
                    CurrencyCode::DKK => CurrencyCode::DKK,
                    CurrencyCode::EUR => CurrencyCode::EUR,
                    CurrencyCode::CHF => CurrencyCode::CHF,
                    CurrencyCode::GBP => CurrencyCode::GBP,
                ],
            ])
            ->add(
                'user',
                EntityType::class,
                [
                    'required'    => false,
                    'class'       => CmUser::class,
                    'placeholder' => 'Channel Manager level authentication',
                ]
            )
            ->add('enabled');
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
            ->add('identifier', null, ['label' => 'Partner URN'])
            ->add('name', null, ['label' => 'Partner Name'])
            ->add('status')
            ->add('user')
            ->add('channelManager')
            ->add('enabled')
            ->add('currency')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('connectedAt');
    }

    /**
     * @param RouteCollection $collection
     *
     * @return void
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('refreshRoom', $this->getRouterIdParameter() . '/refreshRoom');
        $collection->add('dataAlignment', $this->getRouterIdParameter() . '/dataAlignment');
    }
}
