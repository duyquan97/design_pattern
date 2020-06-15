<?php

namespace App\Admin;

use App\Model\ImportDataType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;

/**
 * Class ChainingFileAdmin
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ImportDataAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'import_data';

    /**
     *
     * @param RouteCollection $collection
     *
     * @return void
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->remove('create')
            ->add(
                'download',
                $this->getRouterIdParameter() . '/download'
            );
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
            ->add('type')
            ->add('author')
            ->add('imported')
            ->add('createdAt')
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
            ->add('filename', 'string', ['template' => 'ProductAdmin/list__field_file_link.html.twig'])
            ->add('type')
            ->add('author')
            ->add('imported')
            ->add('processedRows')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('errors', 'url', [
                'template' => 'ImportDataAdmin/download_link.html.twig',
            ])
            ->add(
                '_action',
                null,
                [
                    'actions' => [
                        'edit'   => [],
                        'delete' => [],
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
        $formMapper
            ->add('type', ChoiceType::class, [
                'choices' => [
                    ImportDataType::CHAINING_ROOM => ImportDataType::CHAINING_ROOM,
                    ImportDataType::AVAILABILITY => ImportDataType::AVAILABILITY,
                    ImportDataType::EXPERIENCE => ImportDataType::EXPERIENCE,
                ],
            ])
            ->add(
                'file',
                FileType::class,
                [
                    'required' => false,
                    'help'     => 'only CSV files supported',
                ]
            )
            ->add('imported');
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
            ->add('filename')
            ->add('type')
            ->add('author')
            ->add('imported')
            ->add('createdAt')
            ->add('updatedAt');
    }
}
